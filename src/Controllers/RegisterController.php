<?php

declare(strict_types=1);

namespace Controllers;

use Exception;
use Logic\DatabaseException;
use Logic\IncorrectInputException;
use Logic\Router;
use Logic\Validator;
use Models\DatabaseConnector;

class RegisterController extends Controller
{
    private string $page = '../src/Views/register.php';

    public function render(): void
    {
        require_once $this->page; // Load page content
    }

    /**
     * @throws Exception
     */
    public function register(): void
    {
        $validator = new Validator();

        try {
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;
            $passConf = $_POST['password-confirm'] ?? null;
            $pfpImage = $_FILES['profile-image'] ?? null;

            // validate every input
            if ($validator->validateUsername($username) &&
                $validator->validatePassword($password, $passConf) &&
                $validator->validateImage($pfpImage)
            ) {
                // Hash password
                $password = password_hash(
                    password: $password,
                    algo: PASSWORD_DEFAULT,
                );

                $pfpImagePath = 'assets/uploads/profile_images/' . $username . '.' . explode('/', $pfpImage['type'])[1];

                // Save image
                move_uploaded_file(
                    from: $pfpImage['tmp_name'],
                    to: $pfpImagePath,
                );

                // Insert user into database
                DatabaseConnector::insertUser(
                    username: $username,
                    password: $password,
                    profile_image_path: $pfpImagePath,
                );

                // Redirect to login page
                Router::redirect(path: 'login', query: 'success', parameters: 'register');
            }
        } catch (Exception $e) {
            Router::redirect(path: 'register', query: 'error', parameters: $e->getMessage());
        }
    }
}
