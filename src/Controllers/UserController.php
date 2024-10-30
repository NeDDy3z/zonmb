<?php

namespace Controllers;

use Exception;
use Logic\Router;
use Logic\User;

class UserController extends Controller
{
    /**
     * @var string $page
     */
    private string $page = 'src/Views/user.php'; // Import page content

    /**
     * @var array|string[] $userRole
     */
    private array $userRole = [
        'admin' => 'Administrátor',
        'user' => 'Uživatel',
        'owner' => 'Vlastník'
    ];

    /**
     *
     */
    public function __construct()
    {
        $this->redirectHostUser();
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
        $userRole = $this->userRole;

        require_once $this->page; // Load page content
    }

    /**
     * Load user data if user is logged in
     * @return User
     */
    private function loadUserData(): User
    {
        // If is user logged in proceed, else redirect to login page
        if (isset($_SESSION['username'])) {
            $_SESSION['cache_time'] = $_SESSION['cache_time'] ?? 0;

            // If user_data have been set and aren't older more than ~30 minutes, load them, else pull new from database
            if (isset($_SESSION['user_data']) && (time() - $_SESSION['cache_time'] < 1800)) {
                $user = $_SESSION['user_data'];

            } else {
                try {
                    $user = new User($_SESSION['username']);
                    $_SESSION['user_data'] = $user;
                    $_SESSION['cache_time'] = time();

                    return $user;

                } catch (Exception $e) {
                    Router::redirect(path: 'login', query: 'error', parameters: 'invalid-username');
                }
            }
        } else {
            Router::redirect(path: 'login', query: 'error', parameters: 'not-logged-in');
            $user = new User('');
        }

        return $user ?? new User('');
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

}
