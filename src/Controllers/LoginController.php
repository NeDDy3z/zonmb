<?php
declare(strict_types=1);

namespace Zonmb\Controllers;

use Zonmb\Logic\DatabaseException;
use Zonmb\Logic\Router;
use Zonmb\Models\DatabaseConnector;

class LoginController implements IController {

    private string $path = 'src/Views/login.php';

    public function render(): void {
        require_once $this->path; // Load page content
    }

    /**
     * @throws DatabaseException
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            // validate if request contains username and password - if not redirect to login page
            if ($username === '' || $password === '') {
                Router::redirect(path: 'login', query: 'error', parameters: 'Chybí údaje');
            }

            // Get data from database
            $databaseData = DatabaseConnector::selectUser(username: $username);

            // validate if user exists in the database
            if (!$databaseData) {
                Router::redirect(path: 'login', query: 'error', parameters: 'empty-values');
            }

            // Validate password
            if ($this->validateUserCredentials(
                username: $username, databaseUsername: $databaseData['username'],
                password: $password, databasePassword:  $databaseData['password']
            )) {
                $_SESSION['valid'] = true;
                $_SESSION['timeout'] = time();
                $_SESSION['username'] = $_POST['username'];

                Router::redirect(path: 'user', query: 'success', parameters:  'login-success');
            }
            else {
                Router::redirect(path: 'login', query: 'error', parameters: 'invalid-password');
            }
        }
    }

    // Validate credentials
    private function validateUserCredentials(string $username, string $databaseUsername,string $password, string $databasePassword): bool
    {
        return ($username == $databaseUsername && password_verify($password, $databasePassword));
    }

}