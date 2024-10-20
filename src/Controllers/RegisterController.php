<?php

namespace Controllers;

use Exception;
use http\Header;
use Logic\DatabaseException;
use Logic\IncorrectInputException;
use Logic\Router;
use Models\DatabaseConnector;

class RegisterController implements IController {

    private string $page = 'src/Views/register.php';

    public function render(): void {
        require_once $this->page; // Load page content
    }

    /**
     * @throws Exception
     */
    public function register(): void {
        try {
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;
            $passConf = $_POST['password-confirm'] ?? null;
            $pfpPath = $_POST['profile-image-path'] ?? null;

            // validate every input
            if ($this->validateUsername($username) && $this->validatePassword($password, $passConf) /*&& $this->validateImage($_FILES['profile-image'])*/) {

                // Hash password
                $password = password_hash(password: $password, algo: PASSWORD_DEFAULT);

                // Insert user into database
                DatabaseConnector::insertUser(username: $username, password: $password, profile_image_path: $pfpPath);

                // Redirect to login page
                Router::redirect(path: 'login', query: 'success', parameters: 'registration-success');
            }
        } catch (Exception $e) {
            Router::redirect(path: 'register', query: 'error', parameters: $e->getMessage());
        }
    }

    /**
     * @throws IncorrectInputException
     * @throws DatabaseException
     */
    private function validateUsername(string $username): bool {
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

        if ($error) throw new IncorrectInputException($error);
        return True;
    }

    /**
     * @throws IncorrectInputException
     */
    private function validatePassword(string $password, string $passwordConfirm): bool {
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
        if (strlen($password) < 5 || strlen($password) > 100) {
            $error .= 'invalid-password-size.';
        }

        // Regex
        if (!preg_match('/(?=.*[A-Z])(?=.*\d)/', $password)) {
            $error .= 'invalid-password-regex';
        }

        if ($error) throw new IncorrectInputException($error);
        return True;
    }


    // TODO: finish the file upload
    /**
     * @throws IncorrectInputException
     */
    private function validateImage($image): bool {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Get image file info
            $fileTmp = $_FILES['image']['tmp_name'];
            $fileType = mime_content_type($fileTmp); // validate MIME type
            $imageSize = getimagesize($fileTmp); // Get image dimensions

            // validate if the file is PNG or JPG
            $validTypes = ['image/png', 'image/jpeg'];
            if (!in_array($fileType, $validTypes)) {
                throw new IncorrectInputException('Invalid image type. Only PNG and JPG are allowed.');
            } elseif ($imageSize[0] > 512 || $imageSize[1] > 512) {
                // validate if the image exceeds the dimensions of 512x512 pixels
                echo "Image exceeds the maximum allowed dimensions of 512x512 pixels.";
            } else {
                echo "Image is valid!";
                // Proceed with further processing (e.g., save the image)
            }
        }
        return True;
    }
}
