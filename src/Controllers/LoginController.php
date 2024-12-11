<?php

declare(strict_types=1);

namespace Controllers;

use Logic\DatabaseException;
use Logic\Router;
use Logic\User;
use Logic\Validator;
use Models\UserModel;

class LoginController extends Controller
{
    /**
     * @var string $page
     */
    private string $path = ROOT . 'src/Views/login.php';

    /**
     * @var Validator $validator
     */
    private Validator $validator;


    /**
     * Construct
     */
    public function __construct()
    {
        $this->validator = new Validator();
    }

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
                Router::redirect(path: 'login', query: ['error' => 'emptyValues']);
            }

            // Get data from database
            $databaseData = UserModel::selectUser(username: $username);

            // Validate if user exists in the database
            if (!$databaseData) {
                Router::redirect(path: 'login', query: ['error' => 'loginError']);
            }

            // Validate user<->password
            if ($this->validator->validateUserCredentials(
                username: $username,
                databaseUsername: (string)$databaseData['username'],
                password: $password,
                databasePassword: (string)$databaseData['password'],
            )) {
                if (!isset($_SESSION)) {
                    session_start();
                }

                $_SESSION['username'] = $username;
                $_SESSION['user_data'] = User::getUserByUsername($username);

                $_SESSION['valid'] = true;
                $_SESSION['timeout'] = time();

                Router::redirect(path: 'users/'. $username, query: ['success' => 'login']);
            } else {
                Router::redirect(path: 'login', query: ['error' => 'loginError']);
            }
        }
    }
}
