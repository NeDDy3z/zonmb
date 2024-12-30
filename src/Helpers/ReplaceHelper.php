<?php

namespace Helpers;

/**
 * ReplaceHelper
 *
 * This helper class provides methods for processing strings by replacing special characters
 * and converting strings into URL-friendly formats. It is useful for creating clean and
 * SEO-friendly URLs or for general string normalization.
 *
 * @package Helpers
 * @author Erik Vaněk
 */
class ReplaceHelper
{
    /**
     * Replace special characters in a string with their ASCII equivalents.
     *
     * This method uses `iconv` to transliterate a UTF-8 string into an ASCII representation,
     * removing or approximating any special characters that cannot be directly mapped to ASCII.
     *
     * @param string $string The input string containing special characters.
     *
     * @return string The string with special characters replaced.
     */
    public static function replaceSpecialChars(string $string): string
    {
        return (string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    }

    /**
     * Convert a string into a URL-friendly format.
     *
     * This method creates a hyphen-separated, lowercase string that can be used safely
     * as part of a URL. It replaces spaces with hyphens (`-`), converts special characters
     * to their ASCII equivalents, and ensures the result is in lowercase.
     *
     * Example:
     * - Input:  "Český text"
     * - Output: "cesky-text"
     *
     * @param string $string The input string to process.
     *
     * @return string The URL-friendly string.
     */
    public static function getUrlFriendlyString(string $string): string
    {
        return strtolower(self::replaceSpecialChars(str_replace(' ', '-', $string)));
    }
}
