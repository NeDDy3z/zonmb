<?php
/**
 * TestingController
 *
 * This class is designed strictly for development purposes and allows developers
 * to test specific features in a sandboxed environment. It is intended for internal use
 * only and will be removed upon deployment to production.
 *
 * @package Controllers
 */

namespace Controllers;

use Helpers\PrivilegeRedirect;

class TestingController extends Controller
{
    /**
     * @var string $page Path to the main testing page view
     */
    private string $page = ROOT . 'src/Views/test.php'; // Import page content

    /**
     * @var string $subPage The specific subpage being tested
     */
    private string $subPage;


    /**
     * TestingController constructor
     *
     * Initializes the class with an optional subpage for testing.
     *
     * @param string|null $subPage The specific subpage to handle, defaults to null
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
            default:
                break;
        }
    }


    /**
     * Render the testing page view
     *
     * Loads the main testing view file associated with the `$page` property.
     *
     * @return void
     */
    public function render(): void
    {
        require_once $this->page; // Load page content
    }

    /**
     * Test image uploading functionality
     *
     * This function facilitates testing of the file upload functionality by
     * saving an uploaded test image file (`test-image`) to a predefined location.
     * @return void
     */
    public function testImageUpload(): void
        /**
         * Move an uploaded file to the `assets/uploads/` location.
         *
         * @return void
         */
    {
        $image = $_FILES['test-image']; // Retrieve the uploaded image file

        move_uploaded_file(
            from: $image['tmp_name'],
            to: 'assets/uploads/test.png',
        );
    }

    /**
     * Test XHR response for AJAX requests
     *
     * Returns a JSON response to test client-side XHR functionality.
     *
     * @return void
     */
    public function testXhr(): void
    {
        echo json_encode(['status' => 'test']);
        exit();
    }
}
