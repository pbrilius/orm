<?php

declare(strict_types=1);

namespace App\Http;

/**
 * MVC Router - vanilla PHP routing, no laminas/diactoros.
 */
class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function put(string $path, callable $handler): void
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete(string $path, callable $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function dispatch(Request $request): ?Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $pattern => $handler) {
            $params = $this->match($pattern, $path);
            if ($params !== false) {
                return $handler($request, $params);
            }
        }

        return null;
    }

    private function match(string $pattern, string $path): array|false
    {
        $pattern = rtrim($pattern, '/');
        $path = rtrim($path, '/');

        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            array_shift($matches);
            $params = [];
            preg_match_all('/\{([a-zA-Z_]+)\}/', $pattern, $paramNames);
            foreach ($paramNames[1] as $i => $name) {
                $params[$name] = $matches[$i] ?? null;
            }
            return $params;
        }

        return false;
    }
}
