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
     * @var string
     */
    private string $subPage;

    /**
     * Construct
     */
    public function __construct(?string $subPage = null)
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $privilegeRedirect->redirectEditor();

        $this->subPage = $subPage ?? '';

        switch ($this->subPage) {
            case 'xhr':
                $this->testXhr();
                break;
            default: break;
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

    /**
     * @return void
     */
    public function testXhr(): void
    {
        echo json_encode(['status' => 'test']);
        exit();
    }
}
