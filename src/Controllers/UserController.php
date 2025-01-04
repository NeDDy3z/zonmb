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


/**
 * UserController
 *
 * Controller for managing user-related actions including viewing, editing,
 * deleting, and updating user data, as well as handling user-specific operations
 * such as editing fullname, profile image updates, and password changes.
 *
 * @package Controllers
 * @author Erik Vaněk
 */
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
     * @var User|null $user The user object of the currently logged-in user
     */
    private ?User $user;

    /**
     * @var Validator $validator Validator instance for validating user data
     */
    private Validator $validator;

    /**
     * @var PrivilegeRedirect $privilegeRedirect PrivilegeRedirect instance for redirecting users
     */
    private PrivilegeRedirect $privilegeRedirect;

    /**
     * @var array|string[] $userRole Dictionary of user roles and their display names
     */
    public array $userRole = [
        'user' => 'Uživatel',
        'editor' => 'Editor',
        'admin' => 'Administrátor',
        'owner' => 'Vlastník',
    ];


    /**
     * Constructor
     *
     * Initializes the controller with a given action and ensures only authenticated users can use it.
     * Redirects unauthorized users and handles role-specific restrictions for certain actions.
     */
    public function __construct()
    {
        $this->validator = new Validator();
        $this->privilegeRedirect = new PrivilegeRedirect();

        // Get user data
        $this->user = $_SESSION['user_data'] ?? null;
        $this->privilegeRedirect->redirectHost();

        if (!isset($this->user)) {
            User::logout(false);
            Router::redirect(path: 'login', query: ['error' => 'notLoggedIn']);
        }
    }

    /**
     * Render the appropriate webpage based on the action.
     *
     * Handles rendering for user-related views such as user profile or the user editor.
     * Ensures the data loaded corresponds to the currently authenticated user.
     *
     * @return void
     * @throws Exception If a user is not authorized or data fails to load.
     */
    public function render(): void
    {
        if (isset($this->user)) {
            $user = $this->loadUserData();
        } else {
            User::logout(false);
            Router::redirect(path: 'login', query: ['error' => 'notLoggedIn']);
        }

        $userRoles = $this->userRole;
        require_once $this->page; // Load page content
    }

    /**
     * Render the appropriate webpage based on the action.
     *
     * Handles rendering of the user editor.
     * Ensures the data loaded corresponds to the currently edited user.
     *
     * @return void
     * @throws Exception If a user is not authorized or data fails to load.
     */
    public function renderEditor(): void
    {
        $this->privilegeRedirect->redirectEditor();

        if (!isset($_GET['id'])) {
            Router::redirect(path: 'admin', query: ['error' => 'missingID']);
        }

        $editedUser = User::get(id: $_GET['id']);

        if (!isset($editedUser)) {
            Router::redirect(path: 'admin', query: ['error' => 'incorrectID']);
        }

        $this->privilegeRedirect->redirectUserEditing($editedUser);
        require_once $this->editorPage;
    }

    /**
     * Retrieve logged-in user data.
     *
     * Fetches a user role, and outputs the data as a JSON response.
     *
     * @return void
     */
    public function getMe(): void
    {
        if ($this->user === null) {
            echo json_encode(['error' => 'notLoggedIn']);
            exit();
        }

        echo json_encode([
            'id' => $this->user->getId(),
            'username' => $this->user->getUsername(),
            'fullname' => $this->user->getFullname(),
            'role' => $this->user->getRole(),
            'pfp' => $this->user->getImage(),
            'created_at' => $this->user->getCreatedAt(),
        ]);
        exit();
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
     * Check if the username exists in the database.
     *
     * @return void
     */
    public function existsUsername(): void
    {
        try {
            $username = $_GET['username'] ?? null;

            if (!isset($username)) {
                throw new Exception('missingUsername');
            }

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
        // If a user is logged in proceed, else redirect to login page
        if (isset($this->user) and $_SESSION['user_data']) {
            $_SESSION['cache_time'] = $_SESSION['cache_time'] ?? 0;

            // If user_data have been set and aren't older more than ~30 minutes, load them, else pull new from a database
            if (time() - $_SESSION['cache_time'] > 1800) {
                try {
                    // Re-set user data to session and set expiration
                    $user = User::get(id: $this->user->getId());

                    if (!$user) {
                        Router::redirect(path: 'login', query: ['error' => 'Could not load user data']);
                    }

                    $_SESSION['user_data'] = $user;
                    $_SESSION['cache_time'] = time();

                    return $user;

                } catch (Exception $e) {
                    Router::redirect(path: 'login', query: ['error' => 'notLoggedIn']);
                }
            }
        } else {
            Router::redirect(path: 'login', query: ['error' => 'notLoggedIn']);
        }

        return $this->user;
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

            // Check data etc..
            $this->validator->validateUsername($username, false);
            $this->validator->validateFullname($fullname);

            // Prevent setting an owner
            if ($role === 'owner') {
                $role = 'admin';
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
     */
    public function updateProfileImage(): void
    {
        try {
            $pfpImage = $image ?? ImageHelper::getUsableImageArray($_FILES['image'])[0] ?? null;

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

            Router::redirect(path: 'users/' . $this->user->getUsername(), query: ['success' => 'imageUpload']);
        } catch (Exception $e) {
            Router::redirect(path: 'users/' . $this->user->getUsername(), query: ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the password of the logged-in user.
     *
     * Handles validating, and replacing the user's password. Alsoupdates the database.
     * @return void
     */
    public function updatePassword(): void
    {
        try {
            $oldPassword = $_POST['password-old'] ?? null;
            $newPassword = $_POST['password'] ?? null;
            $newPasswordConfirm = $_POST['password-confirm'] ?? null;
            $userOldPasswordDB = UserModel::selectUserPassword($this->user->getId());

            // Validate passwords
            if (!isset($oldPassword)) {
                throw new Exception('missingOldPassword');
            }
            if (!password_verify($oldPassword, $userOldPasswordDB)) {
                throw new Exception('oldPasswordIncorrect');
            }
            $this->validator->validatePassword($newPassword, $newPasswordConfirm);

            // Change passwd in db
            UserModel::updateUserPassword(
                id: $this->user->getId(),
                password: password_hash($newPassword, PASSWORD_DEFAULT),
            );

            Router::redirect(path: 'users/' . $this->user->getUsername(), query: ['success' => 'passwordChanged']);
        } catch (Exception $e) {
            Router::redirect(path: 'users/' . $this->user->getUsername(), query: ['error' => $e->getMessage()]);
        }
    }



    /**
     * Delete a user pfp
     *
     * Removes the image from the server and updates the database record.
     *
     * @param int|null $id
     * @param string|null $image
     * @return void
     */
    public function deleteUserProfileImage(?int $id, ?string $image): void
    {
        if (!isset($id) or !isset($image)) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
            $user = User::get(id: $id);

            $this->privilegeRedirect->redirectUserEditing($user);

            UserModel::updateUserProfileImage(
                id: $id,
                profile_image_path: DEFAULT_PFP,
            );

            foreach ((array)scandir('assets/uploads/profile_images') as $file) { // Remove pfp
                if (str_starts_with($file, $user->getUsername())) { // if the file starts with the username remove it
                    unlink('assets/uploads/profile_images/' . $file);
                }
            }

            echo json_encode(['success' => 'userImageDeleted']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * Delete user and all its associated data.
     *
     * Ensures that all images related to the user are removed from the server
     * when the user is deleted.
     *
     * @param int|null $id
     * @return void
     */
    public function deleteUser(?int $id): void
    {
        if (!isset($id)) {
            echo json_encode(['error' => 'missingID']);
            exit();
        }

        try {
            $user = User::get(id: $id);

            $this->privilegeRedirect->redirectUserEditing($user);

            UserModel::removeUser(id: $id);
            $this->delteUserImageFromServer($user->getUsername());

            echo json_encode(['success' => 'userDeleted']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    /**
     * Delete user profile image from server file system
     *
     * @param string $username
     * @return void
     */
    private function delteUserImageFromServer(string $username): void
    {
        foreach ((array)scandir('assets/uploads/profile_images') as $file) { // Remove pfp
            if ($username !== false and str_starts_with($file, $username)) { // if the file starts with the username remove it
                unlink('assets/uploads/profile_images/' . $file);
            }
        }
    }

}
