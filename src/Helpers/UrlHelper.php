<?php

declare(strict_types=1);

namespace Helpers;

/**
 * UrlHelper
 *
 * This helper class provides utility methods for constructing and working with URLs.
 * It is specifically designed for use on the `zwa.toad.cz` server, or any setup
 * using a similarly defined base URL in the configuration.
 *
 * @package Helpers
 */
class UrlHelper
{
    /**
     * Get the base URL with an optional appended path.
     *
     * This method generates the full URL by combining the base URL (set in `config.php` or `config.local.php`)
     * with the specified path. It ensures the path is properly trimmed to avoid double slashes.
     *
     * @param string $path The optional path to append to the base URL (default: empty string).
     *
     * @return string The constructed full URL.
     */
    public static function baseUrl(string $path = ''): string
    {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}
