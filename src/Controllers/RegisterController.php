<?php

declare(strict_types=1);

namespace Controllers;

use Exception;
use Helpers\ImageHelper;
use Logic\Router;
use Logic\Validator;
use Models\DatabaseConnector;
use Models\UserModel;

class RegisterController extends Controller
{
    /**
     * @var string $page
     */
    private string $page = ROOT . 'src/Views/register.php';

    private Validator $validator;


    /**
     * Construct
     */
    public function __construct()
    {
        $this->validator = new Validator();
    }


    /**
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        require_once $this->page; // Load page content
    }

    /**
     * Register function
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


            // validate every input
            $this->validator->validateUsername($username);
            $this->validator->validateFullname($fullname);
            $this->validator->validatePassword($password, $passConf);
            $this->validator->validateImage($pfpImage);

            // Hash password
            $password = password_hash(
                password: $password,
                algo: PASSWORD_DEFAULT,
            );

            // Save image
            if ($pfpImage['tmp_name'] !== '') {
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
