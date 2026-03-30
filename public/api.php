<?php

declare(strict_types=1);

/**
 * API Entry Point - Uses laminas/diactoros for PSR-7/PSR-15.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use Laminas\Diactoros\ServerRequestFactory;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$request = ServerRequestFactory::fromGlobals();
$response = $kernel->handle($request);

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value));
    }
}
http_response_code($response->getStatusCode());
echo $response->getBody();

$kernel->terminate();
