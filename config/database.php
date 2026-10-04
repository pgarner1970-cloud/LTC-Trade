<?php
/**
 * Database configuration.
 * Secrets are read from environment variables or the untracked site-root .env file.
 */
$root = dirname(__DIR__);
$envFile = $root . '/.env';

if (is_readable($envFile)) {
    $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (is_array($env)) {
        foreach ($env as $key => $value) {
            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }
        }
    }
}

$host = getenv('DB_HOST');
$name = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

if (!$host || !$name || !$user || $pass === false || $pass === '') {
    throw new RuntimeException('Database configuration is incomplete.');
}

return [
    'dsn' => 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4',
    'user' => $user,
    'pass' => $pass,
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
