<?php

namespace Logic;

use Exception;
use Models\ArticleModel;
use Models\UserModel;

/**
 * Validator
 *
 * This class provides validation utilities for various input types, including user credentials,
 * usernames, passwords, images, and articles. It uses exceptions to handle validation errors,
 * ensuring that invalid input is properly reported.
 *
 * @package Logic
 */
class Validator
{
    const USERNAME_REGEX = '/^[a-zA-Z0-9_.]{3,30}$/';
    const USERNAME_MIN_LENGTH = 3;
    const USERNAME_MAX_LENGTH = 30;

    const FULLNAME_REGEX = '/^[a-zA-ZáčďéěíňóřšťúůýžÁČĎÉĚÍŇÓŘŠŤÚŮÝŽ ]{3,30}$/';
    const FULLNAME_MIN_LENGTH = 3;
    const FULLNAME_MAX_LENGTH = 30;


    const PASSWORD_REGEX = '/^(?=.*[A-Z])(?=.*\d).+$/';
    const PASSWOR_MIN_LENGTH = 5;
    const PASSWORD_MAX_LENGTH = 255;

    const IMAGE_REGEX = '/^image\/(png|jpg|jpeg)$/';

    const TITLE_MIN_LENGTH = 10;
    const TITLE_MAX_LENGTH = 100;
    const SUBTITLE_MIN_LENGTH = 3;
    const SUBTITLE_MAX_LENGTH = 500;
    const CONTENT_MIN_LENGTH = 3;
    const CONTENT_MAX_LENGTH = 5_000;

    /**
     * Throw an exception if there are validation errors.
     *
     * This method converts an array of error messages into a single string and throws
     * an `IncorrectInputException` if any errors exist.
     *
     * @param array<string>|string|null $error The array of error messages, or `null` if no errors.
     *
     * @return bool Returns `true` if no errors are present.
     *
     * @throws IncorrectInputException If any errors are detected.
     */
    private function throwExceptionOnError(array|null $error): bool
    {
        if (isset($error) or $error !== null) {
            $str_error = implode('-', $error);
            throw new IncorrectInputException($str_error);
        }

        return true;
    }

    /**
     * Validate user credentials against the database.
     *
     * This method ensures that the provided username and password match the corresponding
     * records in the database.
     *
     * @param string $username The entered username.
     * @param string $databaseUsername The username from the database.
     * @param string $password The entered password.
     * @param string $databasePassword The hashed password from the database.
     *
     * @return bool Returns `true` if the credentials are valid.
     *
     * @throws IncorrectInputException If the credentials are invalid.
     */
    public function validateUserCredentials(
        string $username,
        string $databaseUsername,
        string $password,
        string $databasePassword,
    ): bool
    {
        $error = null;
        switch (true) {
            case $username !== $databaseUsername:
                $error[] = 'usernameMatch';
                // no break
            case !password_verify($password, $databasePassword):
                $error[] = 'incorrectPassword';
                break;
        }

        // Throw exception on any error
        return $this->throwExceptionOnError($error);
    }

    /**
     * Validate the format and existence of a username.
     *
     * This method validates the username for registration by checking string length,
     * allowed characters (regex), and whether the username is already taken.
     *
     * @param string $username The username to validate.
     *
     * @return bool Returns `true` if the username is valid.
     *
     * @throws DatabaseException If a database-related error occurs.
     * @throws IncorrectInputException If the username is invalid.
     */
    public function validateUsername(string $username, bool $checkExistence = true): bool
    {
        $error = null;
        switch (true) {
            case $username == null || $username == '': // Empty
                $error[] = 'usernameEmpty';
                break;

            case strlen($username) < self::USERNAME_MIN_LENGTH || strlen($username) > self::USERNAME_MAX_LENGTH: // Length
                $error[] = 'usernameSize';

            case !preg_match(self::USERNAME_REGEX, $username): // Regex
                $error[] = 'usernameRegex';

            case $checkExistence and UserModel::existsUser($username): // Exists
                $error[] = 'usernameTaken';
                break;
        }

        // Throw exception on any error
        return $this->throwExceptionOnError($error);
    }

    /**
     * Validate the format and length of a fullname.
     *
     * Used during user registration, this method ensures the fullname adheres to a specific length,
     * format, and character restrictions.
     *
     * @param string $fullname The fullname string to validate.
     *
     * @return bool Returns `true` if the fullname is valid.
     *
     * @throws IncorrectInputException If the fullname is invalid.
     */
    public function validateFullname(string $fullname): bool
    {
        $error = null;
        switch (true) {
            case $fullname == null || $fullname == '': // Empty
                $error[] = 'fullnameEmpty';
                // no break

            case strlen($fullname) < self::FULLNAME_MIN_LENGTH || strlen($fullname) > self::FULLNAME_MAX_LENGTH: // Length
                $error[] = 'fullnameSize';
                // no break

            case !preg_match(self::FULLNAME_REGEX, $fullname): // Regex
                $error[] = 'fullnameRegex';
                break;
        }

        // Throw exception on any error
        return $this->throwExceptionOnError($error);
    }


    /**
     * Validate a password and its confirmation.
     *
     * This method checks that a password meets strength requirements (regex),
     * matches its confirmation, and falls within acceptable length constraints.
     *
     * @param string $password The password entered by the user.
     * @param string $passwordConfirm The confirmation password entered by the user.
     *
     * @return bool Returns `true` if the password is valid.
     *
     * @throws IncorrectInputException If the password is invalid.
     */
    public function validatePassword(
        string $password,
        string $passwordConfirm,
    ): bool
    {
        $error = null;
        switch (true) {
            case $password == null || $passwordConfirm == null || $password == '' || $passwordConfirm == '': // Empty
                $error[] = 'passwordEmpty';
                break;

            case $password != $passwordConfirm: // Matching passwords
                $error[] = 'passwordMatch';
                // no break

            case strlen($password) < self::PASSWOR_MIN_LENGTH || strlen($password) > self::PASSWORD_MAX_LENGTH: // Length
                $error[] = 'passwordSize';
                // no break

            case !preg_match(self::PASSWORD_REGEX, $password): // Regex
                $error[] = 'passwordRegex';
                break;
        }

        // Throw exception on any error
        return $this->throwExceptionOnError($error);
    }

    /**
     * Validate an uploaded image's size, format, and dimensions.
     *
     * This method checks whether an image meets specified requirements, such as file size
     * and width/height constraints.
     *
     * @param array<string, string>|null $image The uploaded image file details (`$_FILES`).
     * @param int $size The maximum allowed size in bytes (default: 2MB).
     * @param int $minWidth The minimum allowed width in pixels (default: 200).
     * @param int $minHeight The minimum allowed height in pixels (default: 200).
     * @param int $maxWidth The maximum allowed width in pixels (default: 4000).
     * @param int $maxHeight The maximum allowed height in pixels (default: 4000).
     *
     * @return bool Returns `true` if the image is valid.
     *
     * @throws IncorrectInputException If the image fails to meet the requirements.
     */
    public function validateImage(
        ?array $image,
        int    $size = 2_000_000, // 1MB - default max size
        int    $minWidth = 200,
        int    $minHeight = 200,
        int    $maxWidth = 4000,
        int    $maxHeight = 4000,
    ): bool {
        // Check if image was really uploaded
        if (!isset($image) or !is_uploaded_file($image['tmp_name'])) {
            throw new Exception('uploadError');
        }

        // Validate last image variables
        $error = null;
        list($width, $height) = getimagesize($image['tmp_name']);
        switch (true) {
            case $image['size'] > $size: // Size in MB...
                $error[] = 'imageSize';
                // no break
            case $width < $minWidth or $width > $maxWidth or
                $height < $minHeight or $height > $maxHeight: // Dimension
                $error[] = 'imageDimensions';
                // no break
        }

        // Throw exception on any error
        return $this->throwExceptionOnError($error);
    }


    /**
     * Validate an article's title, subtitle, and content.
     *
     * This method ensures that inputs for all fields meet length requirements
     * and are not empty.
     *
     * @param string $title The article title.
     * @param string $subtitle The optional article subtitle.
     * @param string $content The article content.
     * @param bool|null $checkExistence
     * @return bool Returns `true` if the article is valid.
     *
     * @throws DatabaseException
     * @throws IncorrectInputException If any validation fails.
     */
    public function validateArticle(string $title, string $subtitle, string $content, ?bool $checkExistence = true): bool
    {
        $error = null;
        switch (true) {
            case $title == null || $title == '': // Empty
                $error[] = 'titleEmpty';
                break;

            case strlen($title) < self::TITLE_MIN_LENGTH || strlen($title) > self::TITLE_MAX_LENGTH: // Length
                $error[] = 'titleSize';
                break;

            case $checkExistence and ArticleModel::existsArticle($title): // Exists
                $article = Article::getArticleByTitle($title);
                if ($article->getTitle() !== $title) {
                    $error[] = 'titleTaken';
                }
                break;

            case $subtitle == null || $subtitle == '':
                $error[] = 'subtitleEmpty';
                break;

            case strlen($subtitle) < self::SUBTITLE_MIN_LENGTH || strlen($subtitle) > self::SUBTITLE_MAX_LENGTH: // Length
                $error[] = 'subtitleSize';
                break;

            case $content == null || $content == '': // Empty
                $error[] = 'contentEmpty';
                break;

            case strlen($content) < self::CONTENT_MIN_LENGTH || strlen($content) > self::CONTENT_MAX_LENGTH: // Length
                $error[] = 'contentSize';
                break;
        }

        // Throw exception on any error
        return $this->throwExceptionOnError($error);
    }
}
