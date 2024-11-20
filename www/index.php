<?php

declare(strict_types=1);

namespace App;

// Initialize config file & load all scripts
require 'config.php';
require_once '../src/loader.php';


use Exception;
use Logic\Router;
use Models\DatabaseConnector;

DatabaseConnector::init();

// Get usable url
$url = substr(strval(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), 1);
$url = str_contains(haystack: $url, needle: '~vanekeri/') ? $url = str_replace(search: '~vanekeri/', replace: '', subject: $url) : $url;
$pages = explode('/', $url);

// Routing
session_start();
try {
    Router::route(url: $url, method: $_SERVER['REQUEST_METHOD']);
} catch (Exception $e) {
    $error_msg = 'Server Error 500. An error has occurred during the webpage rendering - redirecting back to the homepage. Error message: ' . $e->getMessage();
    Router::redirect(path: '', query: 'errorAlert', parameters: $error_msg);
}
