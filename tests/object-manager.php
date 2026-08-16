<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

(new Dotenv())
    ->usePutenv()
    ->bootEnv(__DIR__ . '/.env.local');