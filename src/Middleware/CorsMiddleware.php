<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

class CorsMiddleware implements MiddlewareInterface
{
    private array $allowedOrigins;
    private array $allowedMethods;
    private array $allowedHeaders;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'Accept']
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse(null, 204, $this->getCorsHeaders($request));
        }

        $response = $handler->handle($request);

        return $this->addCorsHeaders($response, $request);
    }

    private function getCorsHeaders(ServerRequestInterface $request): array
    {
        $origin = $request->getHeaderLine('Origin');

        return [
            'Access-Control-Allow-Origin' => $this->getAllowedOrigin($origin),
            'Access-Control-Allow-Methods' => implode(', ', $this->allowedMethods),
            'Access-Control-Allow-Headers' => implode(', ', $this->allowedHeaders),
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Expose-Headers' => 'X-Request-Id, X-RateLimit-Remaining',
        ];
    }

    private function addCorsHeaders(ResponseInterface $response, ServerRequestInterface $request): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');

        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->getAllowedOrigin($origin))
            ->withHeader('Access-Control-Expose-Headers', 'X-Request-Id, X-RateLimit-Remaining');
    }

    private function getAllowedOrigin(string $origin): string
    {
        if (in_array('*', $this->allowedOrigins)) {
            return '*';
        }

        if (in_array($origin, $this->allowedOrigins)) {
            return $origin;
        }

        return $this->allowedOrigins[0] ?? '*';
    }
}
