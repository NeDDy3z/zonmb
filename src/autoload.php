<?php
/**
 * Autoload PHP Classes
 *
 * A custom autoloader function to dynamically load PHP classes.
 * Converts the namespace and class name into a file path and requires the file if it exists.
 * This allows for organizing the project with namespaces and eliminates the need for manual inclusion of each file.
 *
 * @param string $className The fully qualified name of the class being loaded.
 *
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
