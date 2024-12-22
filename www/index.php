<?php
/**
 * ZONMB - website for local deaf community in Mlada Boleslav
 * @author Erik Vaněk <erikvanek0@gmail.com>
 * @version 1.0
 */

declare(strict_types=1);

namespace App;

// Include configuration and autoload scripts.
require 'config.php';
require_once ROOT . 'src/autoload.php';

use Exception;
use Logic\DatabaseException;
use Logic\Router;
use Models\DatabaseConnector;

/**
 * Main application entry point.
 *
 * This script initializes the application by loading required configuration files,
 * setting up the database connection, and performing routing based on the requested URL.
 * It handles critical exceptions for database connectivity and routing, redirecting users
 * to an appropriate error page when needed.
 *
 * @package App
 */

try {
    /**
     * Initialize database connection using credentials from environment variables.
     *
     * @throws DatabaseException If the database cannot be initialized.
     */
    DatabaseConnector::init();
} catch (DatabaseException $e) {
    // Redirect to an error page if a database exception occurs.
    $error_msg = 'Database Error 500. Error message: ' . $e->getMessage();
    Router::redirect(path: '', query: ['popup' => $error_msg]);
}

// Process the requested URL.
$url = substr(strval(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), 1);

// Check if the URL contains a specific prefix (e.g., "vanekeri") and remove it if needed.
$url = str_contains(haystack: $url, needle: '~vanekeri/')
    ? str_replace(search: '~vanekeri/', replace: '', subject: $url)
    : $url;
//$pages = explode('/', $url);


// Routing
session_start();
try {
    /**
     * Route the current request to the appropriate controller and method.
     *
     * @param string $url The requested URL path.
     * @param string $method The HTTP request method (e.g., GET, POST).
     *
     * @throws Exception If the routing fails or an invalid route is accessed.
     */
    Router::route(url: $url, method: $_SERVER['REQUEST_METHOD']);
} catch (Exception $e) {
    // Redirect to an error page if a general server exception occurs.
    $error_msg = 'Server Error 500. Error message: ' . $e->getMessage();
    Router::redirect(path: '', query: ['popup' => $error_msg]);
}
