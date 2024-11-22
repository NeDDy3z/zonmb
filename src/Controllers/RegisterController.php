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

    /**
     * @throws IncorrectInputException
     * @throws DatabaseException
     */
    private function validateUsername(string $username): bool
    {
        $error = null;

        // Validate if its empty
        if ($username == null || $username == '') {
            $error .= 'usernameEmpty-';
        }
        // Size
        if (strlen($username) < 3 || strlen($username) > 30) {
            $error .= 'usernameSize-';
        }
        // Regex
        if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
            $error .= 'usernameRegex-';
        }
        if (count(DatabaseConnector::existsUser($username)) > 0) {
            $error .= 'usernameTaken-';
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
            $error .= 'passwordEmpty-';
        }
        // Password match
        if ($password != $passwordConfirm) {
            $error .= 'passwordMatch-';
        }
        // Size
        if (strlen($password) < 5 || strlen($password) > 50) {
            $error .= 'passwordSize-';
        }
        // Regex
        if (!preg_match('/(?=.*[A-Z])(?=.*\d)/', $password)) {
            $error .= 'passwordRegex-';
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

        if ($image['size'] === 0) {
            return true;
        }

        // Error in uploading
        if ($image['error'] !== UPLOAD_ERR_OK) {
            $error .= 'imageUploadError-';
        }
        // Size
        if ($image['size'] > 1000000) {
            $error .= 'imageSize-';
        }
        // Type
        if (!in_array($image['type'], ['image/png', 'image/jpg', 'image/jpeg'])) {
            $error .= 'imageFormat-';
        }
        // Dimensions
        list($width, $height) = getimagesize($image['tmp_name']);
        if ($width > 500 || $width !== $height) {
            $error .= 'imageDimensions-';
        }

        if ($error) {
            throw new IncorrectInputException($error);
        }

        return true;
    }
}
