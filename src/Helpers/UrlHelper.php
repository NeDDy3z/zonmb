<?php

declare(strict_types=1);

namespace Helpers;

class UrlHelper
{
    public static function baseUrl(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}
