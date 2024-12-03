<?php
// This script is used for loading all logical .php files into the project (apply require_once on them)
// Makes the index.php much more clean :3

/**
 * Autoloader function to load classes dynamically.
 *
 * @param string $className
 * @return void
 */
function autoLoadPHPClasses(string $className): void
{
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $filePath = __DIR__ . '/' . $className . '.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}

// Register the autoloader
spl_autoload_register('autoLoadPHPClasses');
