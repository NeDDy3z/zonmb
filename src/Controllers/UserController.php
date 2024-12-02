<?php

namespace Controllers;

use Exception;
use Logic\DatabaseException;
use Logic\IncorrectInputException;
use Logic\Router;
use Logic\User;
use Logic\Validator;
use Models\DatabaseConnector;

class UserController extends Controller
{
    /**
     * @var string $page
     */
    private string $page = '../src/Views/user.php'; // Import page content

    /**
     * @var array|string[] $userRole
     */
    private array $userRole = [
        'admin' => 'Administrátor',
        'user' => 'Uživatel',
        'owner' => 'Vlastník'
    ];

    /**
     * @var Validator $validator
     */
    private Validator $validator;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->redirectHostUser();
        $this->validator = new Validator();
    }

    /**
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        $this->redirectHostUser();

        // Check if user is logged in & load data
        $user = $this->loadUserData();
        $userRoles = $this->userRole;

        require_once $this->page; // Load page content
    }

    /**
     * Load user data if user is logged in
     * @return User|null
     */
    private function loadUserData(): ?User
    {
        // If user is logged in proceed, else redirect to login page
        if (isset($_SESSION['username'])) {
            $_SESSION['cache_time'] = $_SESSION['cache_time'] ?? 0;

            // If user_data have been set and aren't older more than ~30 minutes, load them, else pull new from database
            if (isset($_SESSION['user_data']) && (time() - $_SESSION['cache_time'] < 1800)) {
                $user = $_SESSION['user_data'];
            } else {
                try {
                    $user = new User($_SESSION['user_data']->getUsername());
                    $_SESSION['user_data'] = $user;
                    $_SESSION['cache_time'] = time();

                    return $user;

                } catch (Exception $e) {
                    Router::redirect(path: 'login', query: 'error', parameters: 'loginInvalidUsername');
                }
            }
        } else {
            Router::redirect(path: 'login', query: 'error', parameters: 'not-logged-in');
        }

        return $user ?? null;
    }

    /**
     * @return void
     * @throws DatabaseException
     */
    public function uploadImage(): void
    {
        $pfpImage = $_FILES['profile-image'] ?? null;

        if (!$pfpImage) {
            Router::redirect(path: 'user', query: 'error', parameters: 'missingImage');
        }

        try {
            $this->validator->validateImage($pfpImage);
        } catch (Exception $e) {
            Router::redirect(path: 'user', query: 'error', parameters: $e->getMessage());
        }

        $pfpImagePath = 'assets/uploads/profile_images/' . $_SESSION['username'] . '.' . explode('/', $pfpImage['type'])[1];

        move_uploaded_file(
            from: $pfpImage['tmp_name'],
            to: $pfpImagePath,
        );

        // Insert user into database
        DatabaseConnector::updateUser(
            id: $_SESSION['user_data']->getId(),
            profile_image_path: $pfpImagePath,
        );

        // Update user data
        $_SESSION['user_data']->setImage($pfpImagePath);

        Router::redirect(path: 'user', query: 'success', parameters: 'pfpUpload');
    }

    /**
     * Redirect user if they are not logged in
     * @return void
     */
    private function redirectHostUser(): void
    {
        if (!isset($_SESSION['username'])) {
            Router::redirect(path: 'login');
        }
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        Router::redirect(path: '', query: 'popup', parameters: 'Odhlášení proběhlo úspěšně');
    }



}
