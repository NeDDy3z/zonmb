<?php
// This script is used for loading all logical .php files into the project (apply require_once on them)
// Makes the index.php much more clean :3

$scriptsDirectory = __DIR__;

$exclude = [
    'loader.php',
    'Views',
];

/**
 * @param string $directory
 * @param array<string> $exclude
 * @return void
 */
function loadPHPfiles(string $directory, array $exclude): void {
    if ($fileHandling = opendir($directory)) {
        while (false !== ($file = readdir($fileHandling))) {
            // Skip these directories, linux or whatever is the reason
            if ($file == '.' || $file == '..') {
                continue;
            }

            $path = $directory . '/' . $file; // Get full path of the script/folder

            // Skip if its in excluded
            if (in_array($file, $exclude)) {
                continue;
            }

            // Folder = go through it again
            if (is_dir($path)) {
                loadPHPfiles(directory: $path, exclude: $exclude);
            }

            // If it's a PHP file, include it
            elseif (pathinfo(path: $file, flags: PATHINFO_EXTENSION) === 'php') {
                require_once $path;
            }
        }

        closedir($fileHandling);
    }
}

loadPHPfiles(directory: $scriptsDirectory, exclude: $exclude);
