<?php
namespace Controllers;

use Exception;
use Helpers\DateHelper;
use Helpers\ImageHelper;
use Helpers\PrivilegeRedirect;
use Logic\DatabaseException;
use Logic\Router;
use Logic\User;
use Logic\Validator;
use Models\UserModel;


class UserController extends Controller
{
    /**
     * @var string $page The path to the user dashboard page
     */
    private string $page = ROOT . 'src/Views/user.php';

    /**
     * @var string $editorPage The path to the user editor page
     */
    private string $editorPage = ROOT . 'src/Views/user-editor.php';

    /**
     * @var string $action The current action being performed
     */
    private string $action;

    /**
     * @var string $username The username of the currently logged-in user
     */
    private string $username;

    /**
     * @var Validator $validator Validator instance for validating user data
     */
    private Validator $validator;

    /**
     * @var array|string[] $userRole Dictionary of user roles and their display names
     */
    private array $userRole = [
        'admin' => 'Administrátor',
        'user' => 'Uživatel',
        'owner' => 'Vlastník'
    ];


    /**
     * Constructor
     *
     * Initializes the controller with a given action and ensures only authenticated users can use it.
     * Redirects unauthorized users and handles role-specific restrictions for certain actions.
     *
     * @param string|null $action The action to be performed (e.g., 'get', 'edit', 'logout')
     */
    public function __construct(?string $action = null)
    {
        // Declare classes
        $this->validator = new Validator();
        $privilegeRedirect = new PrivilegeRedirect();

        // Redirect host user - not logged in user
        $privilegeRedirect->redirectHost('login');

        // Get userdata
        $this->username = $username ?? $_SESSION['user_data']->getUsername();
        $this->action = $action ?? '';

        // Proceed based on action
        switch ($this->action) {
            case 'get': // Return all users - used for admin page
                $privilegeRedirect->redirectEditor();
                $this->getUsers();
                break;
            case 'edit': // Redirect to editing page - for admins
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
     * Render the appropriate webpage based on the action.
     *
     * Handles rendering for user-related views such as user profile or the user editor.
     * Ensures the data loaded corresponds to the currently authenticated user.
     *
     * @throws Exception If user is not authorized or data fails to load.
     * @return void
     */
    public function render(): void
    {
        switch ($this->action) {
            case 'edit':
                $user = User::getUserById($_GET['id'] ?? null);
                break;
            default: // Render logged in user data
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
     * Retrieve users from the database.
     *
     * Fetches user data based on search, sorting, and pagination parameters,
     * and outputs the data as a JSON response.
     *
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

        // Create query
        // Search, Sorting, Paging
        $conditions = ($search) ? "WHERE id LIKE '$search%' OR username LIKE '%$search%' OR fullname LIKE '%$search%' OR 
                                    role LIKE '%$search%' OR created_at LIKE '%$search%'" : "";
        $conditions .= ($sort) ? " ORDER BY $sort" : "";
        $conditions .= ($sortDirection) ? " $sortDirection" : "";
        $conditions .= ($page) ? " LIMIT 10 OFFSET " . ($page - 1) * 10 : "";

        // Get users
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
     * Load the currently authenticated user's data.
     *
     * Retrieves and caches user data in the session for performance. If the cache is older than 30 minutes,
     * it refreshes the user data from the database.
     *
     * @return User|null The logged-in user's data or null if data is not available
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
                    // Set user data to session and set expiration
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
     * Update the full name of the logged-in user.
     *
     * Validates the new full name and saves it in the database. Updates the session data and redirects
     * the user with appropriate feedback.
     *
     * @return void
     */
    public function updateFullname(): void
    {
        $fullname = $_POST['fullname'] ?? null;

        if (!$fullname) {
            Router::redirect(path: 'users/' . $this->username, query: ['error' => 'missingFullname']);
        }

        try {
            // Validate name
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
     * Update the profile image of the logged-in user.
     *
     * Handles uploading, validating, and replacing the user's profile picture. Also
     * updates the database and session to reflect the changes.
     *
     * @throws DatabaseException If there is an issue with the database operation
     * @return void
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

    /**
     * Log out the user.
     *
     * Destroys the session and redirects the user to the home page with a logout confirmation message.
     *
     * @return void
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
        Router::redirect(path: '', query: ['success' => 'logout']);
    }

}
