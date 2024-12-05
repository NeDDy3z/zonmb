<?php

declare(strict_types=1);

namespace App;

// Initialize config file & load all scripts
require 'config.php';
require_once  ROOT . 'src/autoload.php';

use Exception;
use Logic\DatabaseException;
use Logic\Router;
use Logic\User;
use Models\DatabaseConnector;

try {
    DatabaseConnector::init();
} catch (DatabaseException $e) {
    $error_msg = 'Database Error 500. Error message: ' . $e->getMessage();
    Router::redirect(path: '', query: 'popup', parameters: $error_msg);
}

// Get usable url
$url = substr(strval(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), 1);
$url = str_contains(haystack: $url, needle: '~vanekeri/') ? $url = str_replace(search: '~vanekeri/', replace: '', subject: $url) : $url;
//$pages = explode('/', $url);

var_dump($_GET);


// Routing
session_start();
try {
    Router::route(url: $url, method: $_SERVER['REQUEST_METHOD']);
} catch (Exception $e) {
    $error_msg = 'Server Error 500. Error message: ' . $e->getMessage();
    Router::redirect(path: '', query: 'popup', parameters: $error_msg);
}
