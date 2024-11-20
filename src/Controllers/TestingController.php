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
     * @param string $page
     */
    public function __construct()
    {
        if (!isset($_SESSION['user_data']) || $_SESSION['user_data']->getRole() !== 'admin') {
            Router::redirect(''); // Redirect if not logged in as admin
        }
    }


    /**
     * @return void
     */
    public function render(): void
    {
        require_once $this->page; // Load page content
    }

    public function executeTestAction(): void
    {

    }
}
