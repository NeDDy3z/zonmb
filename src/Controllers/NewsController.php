<?php

declare(strict_types=1);

namespace Controllers;

use Logic\DatabaseException;

/**
 * NewsController
 *
 * Responsibilities:
 * - Render the `news.php` view template representing the News page.
 * - Maintain file path information for the view template.
 * - Handle database-related exceptions gracefully during page render.
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class NewsController extends Controller
{
    /**
     * @var string $page Full file path to the news.php view template.
     */
    private string $page = ROOT . 'src/Views/news.php';

    /**
     * Render the News webpage view.
     *
     * This method is used to include and execute the `news.php` view template
     * file, rendering the full News page content. It leverages the `$page`
     * property to retrieve the file location. If a database error occurs
     * during the process, a `DatabaseException` is thrown, ensuring proper
     * error management.
     *
     * @return void
     * @throws DatabaseException If a database-related error occurs.
     */
    public function render(): void
    {
        require_once $this->page; // Load page content
    }
}
