<?php

declare(strict_types=1);

namespace Controllers;

class Controller
{
    /**
     * @var string $page
     */
    private string $page;

    /**
     * Render webpage
     * @return void
     */
    public function render(): void
    {
        require_once $this->page;
    }
}
