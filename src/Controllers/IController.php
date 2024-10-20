<?php
declare(strict_types=1);

namespace Zonmb\Controllers;

interface IController {
    public function render(): void;
}