<?php
/**
 * <h1>Global Application Configuration</h1>
 *
 * This file holds the critical configuration values required for setting up the application's environment.
 * It defines global constants, database connection parameters, file upload settings, error reporting configuration,
 * and other essential global values.
 *
 * <hr>
 * <strong>Note:</strong> To customize these configurations (e.g., for development or specific deployment),
 * create a `config.local.php` file in the same directory. This file will override the default values while
 * the core configuration remains intact.
 */


// Set global configs
/**
 * Absolute path to the root directory of the application.
 *
 * This constant is used as the base reference for locating application resources.
 *
 * @var string ROOT
 */
define('ROOT', __DIR__ . '/../');

/**
 * Absolute path to the Views directory of the application.
 *
 * Used to load the HTML templates or views from the Views folder.
 *
 * @var string VIEWS
 */
define('VIEWS', ROOT . 'src/Views/');

/**
 * Default environment variables for the database connection.
 *
 * Contains the necessary credentials and connection details for the database server.
 *
 * @var array $_ENV['database']
 */
$_ENV['database'] = [
    'server' => 'localhost',
    'dbname' => 'vanekeri',
    'username' => 'vanekeri',
    'password' => 'petrpaveluwu',
];

// Configure PHP settings for file uploads
ini_set('file_uploads', '1');               // Enable file uploads
ini_set('upload_max_filesize', '1M');       // Set maximum file size for uploaded files
ini_set('post_max_size', '1M');             // Set maximum size of POST data
ini_set('max_execution_time', '30');        // Maximum script execution time in seconds
ini_set('max_input_time', '60');            // Maximum request input processing time in seconds

// Enable error reporting for development (TODO: Remove in production)
ini_set('display_errors', '1');              // Display runtime errors
ini_set('display_startup_errors', '1');      // Display startup errors
error_reporting(E_ALL);                      // Report all errors and warnings

// Load local configuration
if (file_exists(ROOT . 'www/config.local.php')) {
    require ROOT . 'www/config.local.php';
    return;
}

// Define default global constants
if (!defined('BASE_URL')) {
    /**
     * The base URL of the application. Defaults to an empty string (root).
     *
     * @var string BASE_URL
     */
    define('BASE_URL', '');
}

if (!defined('DEFAULT_PFP')) {
    /**
     * The default profile picture path for user accounts.
     *
     * @var string DEFAULT_PFP
     */
    define('DEFAULT_PFP', 'assets/uploads/profile_images/_default.png');
}
