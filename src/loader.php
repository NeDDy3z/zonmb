<?php
// This script is used for loading all logical .php files into the project (apply require_once on them)
// Makes the index.php much more clean :3

$srcDirectory = __DIR__;

$projectFiles = [
    'Controllers' => [
        'IController.php', // Has priority
        'ErrorController.php',
        'HomepageController.php',
        'LoginController.php',
        'NewsController.php',
        'RegisterController.php',
        'UserController.php',
    ],
    'Logic' => [
        'DatabaseException.php',
        'IncorrectInputException.php',
        'Router.php',
        'User.php',
    ],
    'Models' => [
        'DatabaseConnector.php',
    ],
];

/**
 * @param array<string|mixed> $projectFiles
 * @param string $rootDirectory
 * @return void
 */
function loadPHPfiles(array $projectFiles, string $rootDirectory): void {
    $i = 0;
    foreach ($projectFiles as $dir) {
        $i += 1;
        foreach ($dir as $file) {
            require_once $rootDirectory .'/'. array_keys($projectFiles)[$i - 1] .'/'. $file;
        }
    }
}

loadPHPfiles(projectFiles: $projectFiles, rootDirectory: $srcDirectory);
