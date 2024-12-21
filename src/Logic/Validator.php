<?php

namespace Logic;

use Models\UserModel;

class Validator
{
    /**
     * Validate if user entered correct username and password that match the databse - used for login
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

    /**
     * Validate username (regex, length, ...) - used for registration
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
     * Validate fullname - used for registration TODO: Add validation
     * @param string $fullname
     * @return bool
     * @throws IncorrectInputException
     */
    public function validateFullname(string $fullname): bool
    {
        $error = null;
        switch (true) {
            case $fullname == null || $fullname == '': // Empty
                $error[] = 'fullnameEmpty';
                // no break

            case strlen($fullname) < 3 || strlen($fullname) > 30: // Length
                $error[] = 'fullnameSize';
                // no break

            case !preg_match('/^[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ ]+$/', $fullname): // Regex
                $error[] = 'fullnameRegex';
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
     * Validate password format and if passwords match - used for registration
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

    // TODO: Refactor image validation
    /**
     * Validate image (size, format, dimensions) - used for registration
     * @param array<string, string> $image
     * @param int|null $size
     * @param array<string>|null $type
     * @param array<int>|null $dimensions
     * @return bool
     * @throws IncorrectInputException
     */
    public function validateImage(
        array  $image,
        ?int   $size = 1_000_000, // 1MB - default max size
        ?array $type = ['image/png', 'image/jpg', 'image/jpeg'],
        ?array $dimensions = [500, 500], // width x height
    ): bool {
        for ($i = 0; $i < count($image['error']); $i++) {
            if ((int)$image['size'][$i] === 0) {
                continue;
            } else {
                // Check errors by conditions
                $error = null;
                switch (true) {
                    // In case of an upload error
                    case $image['error'][$i] !== 0:
                        $error[] = 'imageUploadError';
                        break;

                    case (int)$image['size'][$i] > $size: // Size
                        $error[] = 'imageSize';
                        // no break

                    case !in_array($image['type'][$i], $type): // Format
                        $error[] = 'imageFormat';
                        // no break

                    default: // Dimensions
                        var_dump(getimagesize($image['tmp_name'][$i]));
                        list($width, $height) = getimagesize($image['tmp_name'][$i]);
                        if ($width > $dimensions[0] || $height > $dimensions[1]) {
                            $error[] = 'imageDimensions';
                        }
                        break;
                }

                // Throw exception on any error
                if ($error) {
                    $str_error = implode('-', $error);
                    throw new IncorrectInputException($str_error);
                }
            }
        }

        return true;
    }



    // Validate article

    /**
     * Validate article
     * @param string $title
     * @param string|null $subtitle
     * @param string $content
     * @param array|null $images
     * @return bool
     * @throws IncorrectInputException
     */
    public function validateArticle(string $title, ?string $subtitle, string $content, ?array $images = null): bool
    {
        $error = null;
        switch (true) {
            case $title == null || $title == '': // Empty
                $error[] = 'titleEmpty';
                // no break

            case strlen($title) < 3 || strlen($title) > 100: // Length
                $error[] = 'titleSize';
                // no break

            case isset($subtitle) && strlen($subtitle) < 3 || strlen($subtitle) > 1000: // Length
                $error[] = 'subtitleSize';
                // no break

            case $content == null || $content == '': // Empty
                $error[] = 'contentEmpty';
                // no break

            case strlen($content) < 3 || strlen($content) > 10_000: // Length
                $error[] = 'contentSize';
        }

        // Throw exception on any error
        if ($error) {
            $str_error = implode('-', $error);
            throw new IncorrectInputException($str_error);
        } else {
            return true;
        }
    }
}
