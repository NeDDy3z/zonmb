<?php

namespace Controllers;

use Exception;
use Helpers\DateHelper;
use Helpers\ImageHelper;
use Helpers\PrivilegeRedirect;
use Logic\DatabaseException;
use Logic\IncorrectInputException;
use Logic\Router;
use Logic\User;
use Logic\Validator;
use Models\DatabaseConnector;
use Models\UserModel;

class UserController extends Controller
{
    /**
     * @var string $page
     */
    private string $page = ROOT . 'src/Views/user.php'; // Import page content

    /**
     * @var string $editorPage
     */
    private string $editorPage = ROOT . 'src/Views/user-editor.php';

    /**
     * @var string $action
     */
    private string $action;

    /**
     * User page
     * @var string $username
     */
    private string $username;

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
     * @param string|null $username
     * @param string|null $action
     */
    public function __construct(?string $action = null)
    {
        $this->validator = new Validator();
        $privilegeRedirect = new PrivilegeRedirect();
        $privilegeRedirect->redirectHost('login');

        $this->username = $username ?? $_SESSION['user_data']->getUsername();
        $this->action = $action ?? '';

        switch ($this->action) {
            case 'get':
                $privilegeRedirect->redirectEditor();
                $this->getUsers();
                break;
            case 'edit':
                $privilegeRedirect->redirectEditor();
                $this->page = $this->editorPage;
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
     * @throws Exception
     */
    public function render(): void
    {
        switch ($this->action) {
            case 'edit':
                $user = User::getUserById($_GET['id'] ?? null);
                break;
            default:
                $user = $this->loadUserData();
                $userRoles = $this->userRole;
                break;
        }

        // Check if user is logged in & load data
        if ($user->getUsername() === $this->username) {
            require_once $this->page; // Load page content
        } else {
            Router::redirect(path: '', query: ['error' => 'notAuthorized']);
        }
    }

    /**
     * Get users from database
     * @return void
     */
    public function getUsers(): void
    {
        $search = $_GET['search'] ?? null;
        $sort = $_GET['sort'] ?? null;
        $sortDirection = $_GET['sortDirection'] ?? null;
        $page = $_GET['page'] ?? 1;

        // Convert date format
        $search = DateHelper::ifPrettyConvertToISO($search);

        $conditions = ($search) ? "WHERE id LIKE '$search%' OR username LIKE '%$search%' OR fullname LIKE '%$search%' OR 
                                    role LIKE '%$search%' OR created_at LIKE '%$search%'" : "";
        $conditions .= ($sort) ? " ORDER BY $sort" : "";
        $conditions .= ($sortDirection) ? " $sortDirection" : "";
        $conditions .= ($page) ? " LIMIT 10 OFFSET " . ($page - 1) * 10 : "";

        try {
            $usersData = UserModel::selectUsers(
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
     * Update fullname of the user
     * @return void
     */
    public function updateFullname(): void
    {
        $fullname = $_POST['fullname'] ?? null;

        if (!$fullname) {
            Router::redirect(path: 'users/' . $this->username, query: ['error' => 'missingFullname']);
        }

        try {
            $this->validator->validateFullname($fullname);

            // Insert user into database
            UserModel::updateUserFullname(
                id: $_SESSION['user_data']->getId(),
                fullname: $fullname,
            );

            // Update user data
            $_SESSION['user_data']->setFullname($fullname);

            Router::redirect(path: 'users/' . $this->username, query: ['success' => 'fullnameEdited']);

        } catch (Exception $e) {
            Router::redirect(path: 'users/' . $this->username, query: ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update user profile image
     * @return void
     * @throws DatabaseException
     */
    public function updateProfileImage(): void
    {
        try {
            $pfpImage = ImageHelper::getUsableImageArray($_FILES['profile-image'])[0] ?? null;

            // Validate image
            $this->validator->validateImage($pfpImage);

            // Remove old image
            $oldImagePath = $_SESSION['user_data']->getImage();
            if ($oldImagePath !== DEFAULT_PFP and file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }

            // Save new image
            $pfpImagePath = 'assets/uploads/profile_images/' . $_SESSION['username'] . '.jpeg';
            ImageHelper::saveImage(
                image: ImageHelper::processProfilePicture($pfpImage),
                imagePath: $pfpImagePath,
            );

            // Insert user into database
            UserModel::updateUserProfileImage(
                id: $_SESSION['user_data']->getId(),
                profile_image_path: $pfpImagePath,
            );

            // Update user data
            $_SESSION['user_data']->setImage($pfpImagePath);

            Router::redirect(path: 'users/' . $this->username, query: ['success' => 'imageUpload']);
        } catch (Exception $e) {
            Router::redirect(path: 'users/' . $this->username, query: ['error' => $e->getMessage()]);
        }

    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
        Router::redirect(path: '', query: ['success' => 'logout']);
    }

}
