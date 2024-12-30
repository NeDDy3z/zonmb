<?php

declare(strict_types=1);

namespace Controllers;


/**
 * HomepageController
 *
 * This controller is responsible for loading the homepage view
 * It extends the base Controller class and provides a method for rendering
 * the homepage by including the correct PHP view file
 *
 * @package Controllers
 * @author Erik Vaněk
 */
class HomepageController extends Controller
{
    /**
     * @var string $path Path to the homepage view file
     */
    private string $path = ROOT . 'src/Views/homepage.php';


    /**
     * Renders the homepage view.
     *
     * This method includes the PHP file specified by the `$path` property,
     * allowing the homepage to be displayed to the user.
     *
     * @return void
     */
    public function render(): void
    {
        require_once $this->path;
    }
}
