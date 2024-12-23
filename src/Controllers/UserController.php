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
     * @var User $user The user object of the currently logged-in user
     */
    private User $user;

    /**
     * @var Validator $validator Validator instance for validating user data
     */
    private Validator $validator;

    /**
     * @var array|string[] $userRole Dictionary of user roles and their display names
     */
    public array $userRole = [
        'user' => 'Uživatel',
        'admin' => 'Administrátor',
        'owner' => 'Vlastník',
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

        // Put search infront of everything
        if (isset($action) and $action === 'exists') {
            $this->existsUsername($_GET['username'] ?? null);
        }

        // Redirect host user - not logged-in user
        $privilegeRedirect->redirectHost('login');

        // Get userdata
        $this->user = $_SESSION['user_data'];
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
            case 'delete':
                $privilegeRedirect->redirectEditor();
                $this->deleteUser($_GET['id'] ?? null);
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
     * @return void
     * @throws Exception If user is not authorized or data fails to load.
     */
    public function render(): void
    {
        switch ($this->action) {
            case 'edit':
                if (!$this->user->isAdmin()) {
                    Router::redirect(path: 'admin', query: ['error' => 'notAuthorized']);
                }

                $editedUser = User::getUserById($_GET['id'] ?? null);
                break;
            default: // Render logged in user data
                $user = $this->loadUserData();
                $userRoles = $this->userRole;
                break;
        }

        $userRole = $this->userRole;

        // Check if user is logged in & load data
        require_once $this->page; // Load page content
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
     * Check if username exists in the database.
     *
     * @param string $username
     * @return void
     */
    private function existsUsername(string $username): void
    {
        try {
            $exists = UserModel::existsUser($username);
            echo json_encode(['exists' => $exists]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
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
     * Edit user data.
     *
     * Handles uploading, validating, and replacing the user data.
     * Validates the data saves it in the database. Updates data and redirects
     * the user with appropriate feedback.
     *
     * @return void
     */
    public function updateUser(): void
    {
        try {
            $id = $_POST['id'] ?? null;
            $username = $_POST['username'] ?? null;
            $fullname = $_POST['fullname'] ?? null;
            $role = $_POST['role'] ?? null;
            $image = ImageHelper::getUsableImageArray($_FILES['profile-image']);

            // Check data etc..
            $this->validator->validateUsername($username, false);
            $this->validator->validateFullname($fullname);

            // Check images
            if ($image[0]['tmp_name'] === "") {
                unset($image);
            }

            if (isset($image)) {
                $this->updateProfileImage($image);
            }

            // Update data in DB
            UserModel::updateUser(
                id: $id,
                fullname: $fullname,
                role: $role,
            );


            Router::redirect(path: 'admin', query: ['success' => 'userEdited']);
        } catch (Exception $e) {
            Router::redirect(path: 'admin', query: ['error' => 'userEditError', 'errorDetails' => $e->getMessage()]);
        }
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
            Router::redirect(path: 'users/' . $this->user->getUsername(), query: ['error' => 'missingFullname']);
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

            Router::redirect(path: 'users/' . $this->user->getUsername(), query: ['success' => 'fullnameEdited']);
        } catch (Exception $e) {
            Router::redirect(path: 'users/' . $this->user->getUsername(), query: ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the profile image of the logged-in user.
     *
     * Handles uploading, validating, and replacing the user's profile picture. Also
     * updates the database and session to reflect the changes.
     *
     * @return void
     * @throws DatabaseException If there is an issue with the database operation
     */
    public function updateProfileImage(?array $image = null): void
    {
        try {
            $pfpImage = $image ?? ImageHelper::getUsableImageArray($_FILES['profile-image'])[0] ?? null;

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

    /**
     * Delete user and all its associated data.
     *
     * Ensures that all images related to the yser are removed from the server
     * when the user is deleted.
     *
     * @param int|null $id
     * @return void
     */
    private function deleteUser(?int $id): void {
        if (!isset($id)) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
            $user = User::getUserById($id);

            if ($user->getRole() === 'owner') {
                echo json_encode(['error' => 'cannotRemoveOwner']);
                exit();
            }

            UserModel::removeUser(
                id: $id,
            );

            foreach ((array)scandir('assets/uploads/profile_images') as $file) { // Remove pfp
                if (str_starts_with($file, $id)) { // if the file starts with the username remove it
                    unlink('assets/uploads/profile_images/' . $file);
                }
            }

            echo json_encode(['success' => 'userDeleted']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

}
