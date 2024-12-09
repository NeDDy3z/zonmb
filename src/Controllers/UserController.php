<?php

namespace Controllers;

use Exception;
use Helpers\PrivilegeRedirect;
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
    private string $page = ROOT . 'src/Views/user.php'; // Import page content

    /**
     * @var string $subPage
     */
    private string $subPage;

    /**
     * User page
     * @var string $user
     */
    private string $user;

    /**
     * @var Validator $validator
     */
    private Validator $validator;

    /**
     * @var array|string[] $userRole
     */
    private array $userRole = [
        'admin' => 'Administrátor',
        'user' => 'Uživatel',
        'owner' => 'Vlastník'
    ];



    /**
     * Construct
     * @param string|null $user
     * @param string|null $subPage
     */
    public function __construct(?string $user = null, ?string $subPage = null)
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $privilegeRedirect->redirectHost();

        $this->validator = new Validator();

        if (!$user && !$subPage) {
            $privilegeRedirect->redirectEditor();
            $this->getUsers();
        }

        $this->user = $user ?? '';
        $this->subPage = $subPage ?? '';

        switch ($subPage) {
            case 'fullname':
                $this->updateFullname();
                break;
            case 'profile-image':
                $this->uploadImage();
                break;
            case 'logout':
                $this->logout();
                break;
            default:
                break;
        }
    }

    /**
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        // Check if user is logged in & load data
        $user = $this->loadUserData();
        $userRoles = $this->userRole;

        if ($user->getUsername() === $this->user) {
            require_once $this->page; // Load page content
        } else {
            Router::redirect(path: '', query: ['error' => 'notAuthorized']);
        }
    }

    /**
     * Load user data if user is logged in
     * @return User|null
     */
    private function loadUserData(): ?User
    {
        // If user is logged in proceed, else redirect to login page
        if (isset($_SESSION['user_data'])) {
            $_SESSION['cache_time'] = $_SESSION['cache_time'] ?? 0;

            // If user_data have been set and aren't older more than ~30 minutes, load them, else pull new from database
            if (time() - $_SESSION['cache_time'] < 1800) {
                $user = $_SESSION['user_data'];
            } else {
                try {
                    $user = User::getUserByUsername($_SESSION['user_data']->getUsername());
                    $_SESSION['user_data'] = $user;
                    $_SESSION['cache_time'] = time();

                    return $user;

                } catch (Exception $e) {
                    Router::redirect(path: 'login', query: ['error' => 'loginInvalidUsername']);
                }
            }
        } else {
            Router::redirect(path: 'login', query: ['error' => 'not-logged-in']);
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
            Router::redirect(path: 'user', query: ['error' =>   'missingImage']);
        }

        try {
            $this->validator->validateImage($pfpImage);
        } catch (Exception $e) {
            Router::redirect(path: 'user', query: ['error' =>   $e->getMessage()]);
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

        Router::redirect(path: 'user', query: ['success' =>   'pfpUpload']);
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
        Router::redirect(path: '', query: ['popup' =>   'Odhlášení proběhlo úspěšně']);
    }

    /**
     * Get users from database
     * @return void
    */
    public function getUsers(): void
    {
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? null;
        $page = $_GET['page'] ?? 1;

        $conditions = ($search) ? "WHERE id LIKE $search OR username LIKE '$search' OR fullname LIKE '$search' OR 
                                    role LIKE '$search' OR created_at LIKE '$search'" : "";
        $conditions .= ($sort) ? " ORDER BY $sort" : "";
        $conditions .= ($page) ? " LIMIT 10 OFFSET " . ($page - 1) * 10 : "";

        try {
            $usersData = DatabaseConnector::selectUsers(
                conditions: $conditions,
            );

            if (!$usersData) {
                throw new Exception('No articles found');
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit();
        }

        echo json_encode($usersData);
        exit();
    }

}
