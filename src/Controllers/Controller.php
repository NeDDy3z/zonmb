<?php

declare(strict_types=1);

namespace Controllers;


/**
 * Controller
 *
 * This is the base controller class that provides foundational functionality for other controllers.
 * It includes shared methods and properties to handle common controller responsibilities,
 * such as rendering view files.
 *
 * @package Controllers
 */
class Controller
{
    /**
     * @var string $page The absolute path to the view file
     */
    private string $page;

    /**
     * Render the specified webpage.
     *
     * This method includes the view file specified in the `$page` property
     * to display the content to the user.
     *
     * @return void
     */
    protected function render(): void
    {
        require_once $this->page;
    }
}
