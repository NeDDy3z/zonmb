<?php

const ROOT = __DIR__ . '/../';
const VIEWS = ROOT . 'src/Views/';
const BASE_URL = '/~vanekeri/';


// Get local config
//if (file_exists(ROOT . 'config.local.php')) {
//    require ROOT . 'config.local.php';
//    die();
//}

$_ENV['database'] = [
    'server' => 'localhost',
    'dbname' => 'vanekeri',
    'username' => 'vanekeri',
    'password' => 'petrpaveluwu',
];


// Set error reporting
ini_set('display_errors', '1');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set upload files permissions
ini_set('file_uploads', '1');
ini_set('upload_max_filesize', '1M');  // Max file size for uploads
ini_set('post_max_size', '1M');        // Max POST data size
ini_set('max_execution_time', '30');    // Max script execution time in seconds
ini_set('max_input_time', '60');
