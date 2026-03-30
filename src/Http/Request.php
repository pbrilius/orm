<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Vanilla HTTP Request - no laminas/diactoros dependency for MVC.
 */
class Request
{
    private string $method;
    private string $uri;
    private array $get;
    private array $post;
    private array $server;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function get(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function getPath(): string
    {
        $path = parse_url($this->uri, PHP_URL_PATH);
        return $path ?? '/';
    }

    public function getSegment(int $index): ?string
    {
        $segments = explode('/', trim($this->getPath(), '/'));
        return $segments[$index] ?? null;
    }
}
