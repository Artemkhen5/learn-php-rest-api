<?php

declare(strict_types=1);

use api\src\Controller;
use api\src\Database;
use api\src\Model;

require_once __DIR__ . '/vendor/autoload.php';

set_error_handler('\api\src\ErrorHandler::handleError');
set_exception_handler('\api\src\ErrorHandler::handleException');

header('Content-type: application/json; charset=UTF-8');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
    'host' => $_ENV['HOST'],
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
];

$url = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$id = $url[2] ?? null;

if($url[1] !== 'products') {
    http_response_code(404);
    exit();
}

$database = new Database($config['host'], $config['dbname'], $config['user'], $config['password']);

$model = new Model($database);

$controller = new Controller($model);
$controller->handleRequest($_SERVER['REQUEST_METHOD'], $id);