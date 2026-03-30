<?php

declare(strict_types=1);

/**
 * MVC Entry Point - Vanilla PHP, no laminas/diactoros.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\MvcApplication;
use Oryx\ORM\EntityManagerFactory;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/../.env');

$em = EntityManagerFactory::createFromEnv();
$app = new MvcApplication($em);
$app->run();
