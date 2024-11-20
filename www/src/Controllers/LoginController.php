<?php

declare(strict_types=1);

namespace Controllers;

use Logic\DatabaseException;
use Logic\Router;
use Models\DatabaseConnector;

class LoginController extends Controller
{
    private string $path = 'src/Views/login.php';

    /**
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        require_once $this->path; // Load page content
    }


    /**
     * Login logic
     * @return void
     * @throws DatabaseException
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Validate if request contains username and password - if not redirect to login page
            if ($username === '' || $password === '') {
                Router::redirect(path: 'login', query: 'error', parameters: 'empty-values');
            }

            // Get data from database
            $databaseData = DatabaseConnector::selectUser(username: $username);

            // Validate if user exists in the database
            if (!$databaseData) {
                Router::redirect(path: 'login', query: 'error', parameters: 'invalid-username');
            }

            // Validate password
            if ($this->validateUserCredentials(
                username: $username,
                databaseUsername: (string)$databaseData['username'],
                password: $password,
                databasePassword: (string)$databaseData['password']
            )) {
                $_SESSION['valid'] = true;
                $_SESSION['timeout'] = time();
                $_SESSION['username'] = $_POST['username'];

                Router::redirect(path: 'user', query: 'success', parameters: 'login-success');
            } else {
                Router::redirect(path: 'login', query: 'error', parameters: 'invalid-password');
            }
        }
    }

    /**
     * Validate credentials
     * @param string $username
     * @param string $databaseUsername
     * @param string $password
     * @param string $databasePassword
     * @return bool
     */
    private function validateUserCredentials(string $username, string $databaseUsername, string $password, string $databasePassword): bool
    {
        return ($username == $databaseUsername && password_verify($password, $databasePassword));
    }

}
