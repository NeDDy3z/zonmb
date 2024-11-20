<?php

declare(strict_types=1);

namespace Controllers;

use Exception;
use Logic\DatabaseException;
use Logic\IncorrectInputException;
use Logic\Router;
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
        try {
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;
            $passConf = $_POST['password-confirm'] ?? null;
            $pfpImage = $_FILES['profile-image'] ?? null;

            var_dump($_FILES);

            // validate every input
            if ($this->validateUsername($username) &&
                $this->validatePassword($password, $passConf) &&
                $this->validateImage($pfpImage)
            ) {

                // Hash password
                $password = password_hash(
                    password: $password,
                    algo: PASSWORD_DEFAULT,
                );

                // Save image
                move_uploaded_file(
                    from: $pfpImage['tmp_name'],
                    to: 'assets/uploads/profile_images/' . $username,
                );

                // Insert user into database
                DatabaseConnector::insertUser(
                    username: $username,
                    password: $password,
                    profile_image_path: $pfpImage,
                );

                // Redirect to login page
                Router::redirect(path: 'login', query: 'success', parameters: 'register-success');
            }
        } catch (Exception $e) {
            Router::redirect(path: 'register', query: 'error', parameters: $e->getMessage());
        }
    }

    /**
     * @throws IncorrectInputException
     * @throws DatabaseException
     */
    private function validateUsername(string $username): bool
    {
        $error = null;

        // Validate if its empty
        if ($username == null || $username == '') {
            $error .= 'empty-values.';
        }
        // Size
        if (strlen($username) < 3 || strlen($username) > 30) {
            $error .= 'invalid-username-size.';
        }
        // Regex
        if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
            $error .= 'invalid-username-regex.';
        }
        if (count(DatabaseConnector::existsUser($username)) > 0) {
            $error .= 'username-taken';
        }

        if ($error) {
            throw new IncorrectInputException($error);
        }
        return true;
    }

    /**
     * @throws IncorrectInputException
     */
    private function validatePassword(string $password, string $passwordConfirm): bool
    {
        $error = null;

        // validate if its empty
        if ($password == null || $passwordConfirm == null || $password == '' || $passwordConfirm == '') {
            $error .= 'empty-values.';
        }
        // Password match
        if ($password != $passwordConfirm) {
            $error .= 'passwords-dont-match.';
        }
        // Size
        if (strlen($password) < 5 || strlen($password) > 50) {
            $error .= 'invalid-password-size.';
        }

        // Regex
        if (!preg_match('/(?=.*[A-Z])(?=.*\d)/', $password)) {
            $error .= 'invalid-password-regex';
        }

        if ($error) {
            throw new IncorrectInputException($error);
        }
        return true;
    }


    // TODO: finish the file upload

    /**
     * @param $image
     * @return bool
     * @throws IncorrectInputException
     */
    private function validateImage($image): bool
    {
        $error = null;

        // No image = return True
        if ($image == null) {
            return true;
        }

        if ($image['error'] !== UPLOAD_ERR_OK) {
            $error .= 'image-upload-error.';
        }

        if ($image['size'] > 1000000) {
            $error .= 'invalid-image-size.';
        }

        if (!in_array($image['type'], ['image/png', 'image/jpg'])) {
            $error .= 'invalid-image-type.';
        }


        list($width, $height) = getimagesize($_FILES['image']['tmp_name']);
        if ($width > 500 || $width !== $height) {
            $error .= 'invalid-image-dimensions';
        }

        if ($error) {
            throw new IncorrectInputException($error);
        }
        return true;
    }
}
