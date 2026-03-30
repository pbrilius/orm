<?php

declare(strict_types=1);

/**
 * Web Entry Point - Supports both MVC and ADR modes.
 *
 * MVC Mode (default for browser requests):
 * - Uses vanilla PHP: App\Http\Request, App\Http\Response, App\Http\Router
 * - Server-side rendered HTML templates
 * - No laminas/diactoros dependency
 *
 * ADR Mode (for API requests):
 * - Uses laminas/diactoros: ServerRequestFactory, JsonResponse
 * - JSON:API responses
 * - League Fractal transformers
 */

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$isApiRequest = str_starts_with($requestUri, '/api');

if ($isApiRequest) {
    require __DIR__ . '/api.php';
} else {
    require __DIR__ . '/mvc.php';
}
