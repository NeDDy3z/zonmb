<?php

namespace Controllers;

use Logic\Router;

class TestingController extends Controller
{
    /**
     * @var string $page
     */
    private string $page = '../src/Views/test.php'; // Import page content


    /**
     * construct
     */
    public function __construct()
    {
        // Redirect if not logged in as admin
        if (!isset($_SESSION['user_data']) || $_SESSION['user_data']->getRole() !== 'admin') {
            Router::redirect('');
        }
    }


    /**
     * @return void
     */
    public function render(): void
    {
        require_once $this->page; // Load page content
    }

    public function testImageUpload(): void
    {
        $image = $_FILES['test-image'];

        move_uploaded_file(
            from: $image['tmp_name'],
            to: 'assets/uploads/test.png',
        );
    }
}
