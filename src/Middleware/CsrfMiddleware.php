<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * CSRF Protection Middleware.
 *
 * Implements the Synchronizer Token Pattern:
 * 1. Generate unique token per session
 * 2. Embed token in forms (hidden field or header)
 * 3. Validate token on state-changing requests
 *
 * PWA Principle:
 * - SPAs can store tokens in memory/localStorage
 * - Native apps use Authorization headers
 * - Traditional forms require session-based tokens
 */
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    private const TOKEN_LENGTH = 32;
    private const TOKEN_NAME = '_csrf_token';
    private const HEADER_NAME = 'X-CSRF-Token';

    private array $exemptRoutes = [
        'GET' => ['/health', '/manifest.json', '/api/users'],
        'HEAD' => ['/health', '/manifest.json'],
        'OPTIONS' => ['*'],
    ];

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $method = $request->getMethod();
        $path = parse_url((string) $request->getUri(), PHP_URL_PATH) ?? '/';

        if ($this->isExempt($method, $path)) {
            return $handler->handle($request);
        }

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            $token = $this->generateToken();
            $this->storeToken($request, $token);

            $response = $handler->handle($request);
            return $response->withHeader('X-CSRF-Token', $token);
        }

        $token = $this->getSubmittedToken($request);

        if (!$token || !$this->isValidToken($request, $token)) {
            return new \Laminas\Diactoros\Response\JsonResponse([
                '_error' => [
                    'status' => 403,
                    'title' => 'Forbidden',
                    'detail' => 'CSRF token validation failed',
                ],
            ], 403, [
                'Content-Type' => 'application/hal+json',
            ]);
        }

        return $handler->handle($request);
    }

    private function isExempt(string $method, string $path): bool
    {
        if (!isset($this->exemptRoutes[$method])) {
            return false;
        }

        foreach ($this->exemptRoutes[$method] as $pattern) {
            if ($pattern === '*') {
                return true;
            }
            if (str_starts_with($pattern, '/') && $path === $pattern) {
                return true;
            }
            if (str_contains($pattern, '*')) {
                $regex = '#^' . str_replace('*', '.*', $pattern) . '$#';
                if (preg_match($regex, $path)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    private function storeToken(ServerRequestInterface $request, string $token): void
    {
        $_SESSION['_csrf_tokens'][$token] = time();

        if (count($_SESSION['_csrf_tokens'] ?? []) > 50) {
            $_SESSION['_csrf_tokens'] = array_slice(
                $_SESSION['_csrf_tokens'] ?? [],
                -50,
                50,
                true
            );
        }
    }

    private function getSubmittedToken(ServerRequestInterface $request): ?string
    {
        $header = $request->getHeaderLine(self::HEADER_NAME);
        if ($header) {
            return $header;
        }

        $body = json_decode((string) $request->getBody(), true) ?? [];
        if (isset($body[self::TOKEN_NAME])) {
            return $body[self::TOKEN_NAME];
        }

        $parsed = (array) $request->getParsedBody();
        if (isset($parsed[self::TOKEN_NAME])) {
            return $parsed[self::TOKEN_NAME];
        }

        return null;
    }

    private function isValidToken(ServerRequestInterface $request, string $token): bool
    {
        $tokens = $_SESSION['_csrf_tokens'] ?? [];

        if (!isset($tokens[$token])) {
            return false;
        }

        $created = $tokens[$token];
        $maxAge = 3600;

        if ((time() - $created) > $maxAge) {
            unset($_SESSION['_csrf_tokens'][$token]);
            return false;
        }

        unset($_SESSION['_csrf_tokens'][$token]);
        return true;
    }

    public function generateFormToken(string $sessionId): string
    {
        $token = $this->generateToken();
        $_SESSION['_csrf_tokens'][$token] = time();
        return $token;
    }
}
