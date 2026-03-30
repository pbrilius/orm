<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Vanilla HTTP Response - no laminas/diactoros dependency for MVC.
 */
class Response
{
    private int $statusCode;
    private string $content;
    private array $headers;

    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = array_merge(['Content-Type' => 'text/html; charset=UTF-8'], $headers);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header(sprintf('%s: %s', $name, $value));
        }
        
        echo $this->content;
    }
}
