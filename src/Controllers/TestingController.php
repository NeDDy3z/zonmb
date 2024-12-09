<?php

namespace Controllers;

use Helpers\PrivilegeRedirect;
use Logic\Router;

class TestingController extends Controller
{
    /**
     * @var string $page
     */
    private string $page = ROOT . 'src/Views/test.php'; // Import page content


    /**
     * Construct
     */
    public function __construct()
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $privilegeRedirect->redirectEditor();
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
