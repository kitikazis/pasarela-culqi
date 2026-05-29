<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$dotenv->required([
    'DB_HOST', 'DB_NAME', 'DB_USER',
    'CULQI_PUBLIC_KEY',
    'CULQI_PRIVATE_KEY',
    'CULQI_BASE_URL',
])->notEmpty();

// DB_PASS puede existir vacío
$dotenv->required(['DB_PASS']);

return [
    'app' => [
        'env'   => $_ENV['APP_ENV']   ?? 'production',
        'debug' => $_ENV['APP_DEBUG'] ?? false,
    ],
    'db' => [
        'host' => $_ENV['DB_HOST'],
        'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
        'name' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS'] ?? '',
    ],
    'culqi' => [
        'public_key'  => $_ENV['CULQI_PUBLIC_KEY'],   // ← llave pública
        'private_key' => $_ENV['CULQI_PRIVATE_KEY'],  // ← llave privada
        'base_url'    => $_ENV['CULQI_BASE_URL'],
    ],
];