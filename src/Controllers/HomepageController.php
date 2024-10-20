<?php

namespace Controllers;

class HomepageController implements IController {
    public function render(): void {
        $title = "ZONMB";

        require 'src/Views/homepage.php'; // Import page content
    }
}