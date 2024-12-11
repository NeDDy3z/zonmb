<?php

namespace Logic;

use Models\DatabaseConnector;

class Validator
{
    /**
     * Validate username
     * @param string $username
     * @return bool
     * @throws DatabaseException
     * @throws IncorrectInputException
     */
    public function validateUsername(string $username): bool
    {
        $error = null;
        switch (true) {
            case $username == null || $username == '': // Empty
                $error[] = 'usernameEmpty';
                // no break
            
            case strlen($username) < 3 || strlen($username) > 30: // Length
                $error[] = 'usernameSize';
                // no break
            
            case !preg_match('/^[a-zA-Z0-9._]+$/', $username): // Regex
                $error[] = 'usernameRegex';
                // no break
            
            case count(UserModel::existsUser($username)) > 0: // Exists
                $error[] = 'usernameTaken';
                break;
        }

        // Throw exception on any error
        if ($error) {
            $str_error = implode('-', $error);
            throw new IncorrectInputException($str_error);
        } else {
            return true;
        }
    }

    /**
     * Validate fullname TODO: Add validation
     * @param string $fullname
     * @return bool
     */
    public function validateFullname(string $fullname): bool
    {
        return true;
    }

    /**
     * Validate password
     * @param string $password
     * @param string $passwordConfirm
     * @return bool
     * @throws IncorrectInputException
     */
    public function validatePassword(string $password, string $passwordConfirm): bool
    {
        $error = null;
        switch (true) {
            case $password == null || $passwordConfirm == null || $password == '' || $passwordConfirm == '': // Empty
                $error[] = 'passwordEmpty';
                // no break

            case $password != $passwordConfirm: // Matching passwords
                $error[] = 'passwordMatch';
                // no break

            case strlen($password) < 5 || strlen($password) > 50: // Length
                $error[] = 'passwordSize';
                // no break

            case !preg_match('/(?=.*[A-Z])(?=.*\d)/', $password): // Regex
                $error[] = 'passwordRegex';
                break;
        }

        // Throw exception on any error
        if ($error) {
            $str_error = implode('-', $error);
            throw new IncorrectInputException($str_error);
        } else {
            return true;
        }
    }
    
    
    /**
     * Validate image
     * @param array<string, string> $image
     * @param array<string, int | array<int, int | string>> $conditions
     * @return bool
     * @throws IncorrectInputException
     */
    public function validateImage(
        array $image,
        array $conditions = [
            'size' => 1000000,
            'type' => ['image/png', 'image/jpg', 'image/jpeg'],
            'dimensions' => [500, 500],
        ]
    ): bool {
        // Image havent been uploaded = skip validation
        if ((int)$image['size'] === 0) {
            return true;
        }

        // Check errors by conditions
        $error = null;
        switch (true) {
            // In case of an upload error
            case $image['error'] !== UPLOAD_ERR_OK:
                $error[] = 'imageUploadError';
                break;

            case array_key_exists(key: 'size', array: $conditions): // Size
                if ((int)$image['size'] > (int)$conditions['size']) {
                    $error[] = 'imageSize';
                }
                // no break

            case array_key_exists(key: 'type', array: $conditions): // Format
                if (!in_array($image['type'], $conditions['type'])) {
                    $error[] = 'imageFormat';
                }
                // no break

            case array_key_exists(key: 'dimensions', array: $conditions): // Dimensions
                list($width, $height) = getimagesize($image['tmp_name']);
                if ($width > $conditions['dimensions'][0] || $width !== $height) {
                    $error[] = 'imageDimensions';
                }
                break;
        }

        // Throw exception on any error
        if ($error) {
            $str_error = implode('-', $error);
            throw new IncorrectInputException($str_error);
        } else {
            return true;
        }
    }

    /**
     * Validate if user entered correct username and password that match
     * @param string $username
     * @param string $databaseUsername
     * @param string $password
     * @param string $databasePassword
     * @return bool
     */
    public function validateUserCredentials(string $username, string $databaseUsername, string $password, string $databasePassword): bool
    {
        return ($username == $databaseUsername && password_verify($password, $databasePassword));
    }
}
