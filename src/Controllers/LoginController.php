<?php

declare(strict_types=1);

namespace Controllers;

use Exception;
use Logic\Router;
use Logic\User;
use Logic\Validator;
use Models\UserModel;


/**
 * Login page controller
 *
 * Handles rendering the login page and processing user login requests.
 * This class uses a validator to ensure valid input and processes
 * user credentials against the database to allow or deny login attempts.
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class LoginController extends Controller
{
    /**
     * @var string $path The file path to the login view.
     */
    private string $path = ROOT . 'src/Views/login.php';

    /**
     * @var Validator $validator Validator instance used for validating login credentials.
     */
    private Validator $validator;


    /**
     * Constructor.
     *
     * Initializes the validator instance for validating
     * user input during login processing.
     */
    public function __construct()
    {
        $this->validator = new Validator();
    }

    /**
     * Render the login page view.
     *
     * Loads the login.php file as the view for the login page.
     *
     * @return void
     */
    public function render(): void
    {
        require_once $this->path; // Load page content
    }


    /**
     * Process the user login request.
     *
     * Validates provided username and password, checks against
     * the database for matching credentials, and starts
     * a session for successful logins. Redirects accordingly
     * upon success or failure.
     *
     * @return void
     * @throws Exception If validation or authentication fails.
     */
    public function login(): void
    {
        try {
            $username = $_POST['username'] ?? null;
            $password = $_POST['password'] ?? null;

            // Validate if request contains a valid username and password, redirects if invalid.
            $this->validator->validatePassword($password, $password);

            // Retrieve user data from the database based on the username.
            $user = User::get(username: $username);

            // Check if user exists in the database, redirects if not found.
            if (!$user) {
                Router::redirect(path: 'login', query: ['error' => 'loginError']);
            }

            /** @var User $user */
            // Verify the provided username and password against the database credentials.
            $this->validator->validateUserCredentials(
                username: $username,
                databaseUsername: $user->getUsername(),
                password: $password,
                databasePassword: (string)UserModel::selectUserPassword(id: $user->getId()),
            );

            if (!isset($_SESSION)) {
                session_start();
            }

            // Set session
            $_SESSION['user_data'] = $user;
            $_SESSION['valid'] = true;
            $_SESSION['timeout'] = time();

            Router::redirect(path: "user", query: ['success' => 'loggedIn']);
        } catch (Exception $e) {
            Router::redirect(path: 'login', query: ['error' => $e->getMessage()]);
        }
    }

    /**
     * Logout currently logged in user
     *
     * @return void
     */
    public function logout(): void
    {
        User::logout();
    }
}
