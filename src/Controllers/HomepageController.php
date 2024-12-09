<?php

declare(strict_types=1);

namespace Controllers;

class HomepageController extends Controller
{
    /**
     * @var string $path
     */
    private string $path = ROOT . 'src/Views/homepage.php';


    /**
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        require_once $this->path;
    }
}
