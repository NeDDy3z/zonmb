<?php

declare(strict_types=1);

namespace Controllers;

use Exception;
use Helpers\ImageHelper;
use Logic\Router;
use Logic\Validator;
use Models\UserModel;

/**
 * RegisterController
 *
 * This controller manages the process of user registration. It facilitates:
 * - Rendering the registration form view.
 * - Validation of user inputs such as username, full name, password, and profile image.
 * - Processing and saving uploaded profile images securely.
 * - Interacting with the database to save user details.
 * - Redirecting users to appropriate pages (e.g., login or error page).
 *
 * @package Controllers
 */
class RegisterController extends Controller
{
    /**
     * The absolute path to the `register.php` view file.
     *
     * This property holds the file path for the registration page view that will
     * be loaded when executing the render method.
     *
     * @var string
     */
    private string $page = ROOT . 'src/Views/register.php';

    /**
     * Validator instance for validating user input fields.
     *
     * This is used to perform field-specific validation for usernames, passwords,
     * profile images, and any other related data during the user registration process.
     *
     * @var Validator
     */
    private Validator $validator;


     /**
     * RegisterController constructor.
     *
     * Initializes an instance of the RegisterController by creating
     * a new Validator object that will be used for input validation.
     */
    public function __construct()
    {
        $this->validator = new Validator();
    }

    /**
     * Render the registration webpage.
     *
     * This method loads and displays the `register.php` view file from the
     * specified path to present the user with the registration form.
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        require_once $this->page; // Load page content
    }

    /**
     * Process the user registration workflow.
     *
     * This method handles the complete user registration logic, including:
     * - Retrieving and validating submitted form data (username, full name, passwords).
     * - Validating and processing the uploaded profile image.
     * - Hashing passwords securely using PHP's native hashing algorithm.
     * - Saving the user's profile image to a designated directory.
     * - Saving user information in the database via the UserModel.
     * - Redirecting the user to the login page upon success or back to the registration
     *   page with an error message if any step fails.
     *
     * @throws Exception If validation fails or any other error occurs during processing.
     *
     * Check each field if it is correct
     * @throws Exception
     */
    public function register(): void
    {
        try {
            $username = $_POST['username'] ?? null;
            $fullname = $_POST['fullname'] ?? null;
            $password = $_POST['password'] ?? null;
            $passConf = $_POST['password-confirm'] ?? null;
            $pfpImage = ImageHelper::getUsableImageArray($_FILES['profile-image'])[0] ?? null;



            // Validate every input
            $this->validator->validateUsername($username);
            $this->validator->validateFullname($fullname);
            $this->validator->validatePassword($password, $passConf);

            if (isset($pfpImage)) {
                $this->validator->validateImage($pfpImage);
            }

            // Hash password
            $password = password_hash(
                password: $password,
                algo: PASSWORD_DEFAULT,
            );

            // Save image
            if (isset($pfpImage)) {
                $pfpImagePath = "assets/uploads/profile_images/$username.jpeg";
                ImageHelper::saveImage(
                    image: ImageHelper::processProfilePicture($pfpImage),
                    imagePath: $pfpImagePath,
                );
            }

            // Insert user into database
            UserModel::insertUser(
                username: $username,
                fullname: $fullname,
                password: $password,
                profile_image_path: $pfpImagePath ?? null,
            );

            // Redirect to login page
            Router::redirect(path: 'login', query: ['success' => 'register']);
        } catch (Exception $e) {
            Router::redirect(path: 'register', query: ['error' => $e->getMessage()]);
        }
    }
}
