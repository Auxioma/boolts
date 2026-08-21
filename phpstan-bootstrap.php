<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/vendor/autoload.php';

$dotenv = new Dotenv();
$dotenv->usePutenv()->bootEnv(__DIR__.'/.env');

// Keep PHPStan aligned with var/cache/dev/App_KernelDevDebugContainer.xml.
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'dev';
$_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'] = '1';
putenv('APP_ENV=dev');
putenv('APP_DEBUG=1');

$defaults = [
    'APP_SECRET' => 'phpstan',
    'DATABASE_URL' => 'sqlite:///%kernel.project_dir%/var/phpstan.db',
    'DEFAULT_URI' => 'http://localhost',
    'GOOGLE_CLIENT_ID' => 'phpstan',
    'GOOGLE_CLIENT_SECRET' => 'phpstan',
    'MAILER_DSN' => 'null://null',
    'MAPBOX_PUBLIC_TOKEN' => 'phpstan',
    'MAPBOX_PUBLIC_TOKEN_CARD' => 'phpstan',
    'MESSENGER_TRANSPORT_DSN' => 'sync://',
    'STRIPE_SECRET_KEY' => 'sk_test_phpstan',
    'VAR_DUMPER_SERVER' => '127.0.0.1:9912',
];

foreach ($defaults as $name => $value) {
    if (isset($_ENV[$name]) || isset($_SERVER[$name]) || false !== getenv($name)) {
        continue;
    }

    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
    putenv($name.'='.$value);
}
