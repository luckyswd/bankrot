<?php

use Symfony\Component\Dotenv\Dotenv;

// Устанавливаем APP_ENV до загрузки autoload
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0';

require dirname(__DIR__) . '/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env', 'test');
}

$appDebug = $_SERVER['APP_DEBUG'] ?? '0';
if ($appDebug === '1') {
    umask(0000);
}
