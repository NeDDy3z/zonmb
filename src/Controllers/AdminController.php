<?php

namespace Controllers;

use Helpers\PrivilegeRedirect;

/**
 * AdminController
 *
 * The AdminController class handles functionality for the admin panel. It ensures only privileged users can access
 * the admin area and provides tools for managing users and articles, including fetching their data from the database
 * for rendering the admin dashboard.
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class AdminController extends Controller
{
    /**
     * @var string $page Path to the admin dashboard view
     */
    private string $page = ROOT . 'src/Views/admin.php';

    /**
     * Constructor
     *
     * Ensures that only logged-in users with appropriate privileges can access the admin functionalities.
     * Redirects users without proper permissions to the appropriate page.
     */
    public function __construct()
    {
        $privilegeRedirect = new PrivilegeRedirect();
        $privilegeRedirect->redirectEditor();
    }

    /**
     * Render the admin dashboard page.
     *
     * @return void
     */
    public function render(): void
    {
        require_once $this->page; // Load page content
    }
}
