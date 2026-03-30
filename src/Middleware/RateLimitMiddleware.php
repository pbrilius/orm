<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    private int $maxRequests;
    private int $windowSeconds;
    private array $storage = [];

    public function __construct(int $maxRequests = 60, int $windowSeconds = 60)
    {
        $this->maxRequests = $maxRequests;
        $this->windowSeconds = $windowSeconds;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $key = $this->getKey($request);
        $now = time();

        if (!isset($this->storage[$key])) {
            $this->storage[$key] = ['count' => 0, 'window_start' => $now];
        }

        $record = &$this->storage[$key];

        if ($now - $record['window_start'] >= $this->windowSeconds) {
            $record = ['count' => 0, 'window_start' => $now];
        }

        $record['count']++;
        $remaining = max(0, $this->maxRequests - $record['count']);

        if ($record['count'] > $this->maxRequests) {
            return new \Laminas\Diactoros\Response\JsonResponse([
                '_error' => [
                    'status' => 429,
                    'title' => 'Too Many Requests',
                    'detail' => "Rate limit exceeded. Try again in {$this->windowSeconds} seconds.",
                ],
            ], 429, [
                'Content-Type' => 'application/hal+json',
                'Retry-After' => (string) $this->windowSeconds,
                'X-RateLimit-Limit' => (string) $this->maxRequests,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        $response = $handler->handle($request);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) $remaining);
    }

    private function getKey(ServerRequestInterface $request): string
    {
        $ip = $request->getHeaderLine('X-Forwarded-For')
            ?: $request->getHeaderLine('X-Real-IP')
            ?: 'unknown';

        return $ip;
    }

    public function reset(string $key): void
    {
        unset($this->storage[$key]);
    }

    public function resetAll(): void
    {
        $this->storage = [];
    }
}
