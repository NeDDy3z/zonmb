<?php

namespace Controllers;

use Logic\DatabaseException;
use Logic\Router;
use Models\DatabaseConnector;

class LoginController {

    // Render user page
    public function render(): void
    {
        $title = "ZONMB - Přihlášení";

        require 'src/Views/login.php'; // Import page content
    }

    /**
     * @throws DatabaseException
     */
    public function login(): void
    {
        if (isset($_POST)) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Check if request contains username and password - if not redirect to login page
            if ($username === '' || $password === '') {
                Router::redirect(path: 'login', query: 'error', parameters: 'Chybí údaje');
            }

            // Get data from database
            $databaseData = DatabaseConnector::selectUser(username: $username)[0];

            // Check if user exists in the database
            if (!$databaseData) {
                Router::redirect(path: 'login', query: 'error', parameters: 'empty-values');
            }

            // Check password
            if ($this->checkUserCredentials($username, $databaseData['username'], $password, $databaseData['password'])) {
                Router::redirect(path: 'login', query: 'success', parameters:  'login-success');
            }
            else {
                Router::redirect(path: 'login', query: 'error', parameters: 'invalid-password');
            }
        }
    }

    // Check credentials
    private function checkUserCredentials(string $username, string $databaseUsername,string $password, string $databasePassword): bool
    {
        return ($username == $databaseUsername && password_verify($password, $databasePassword));
    }

}