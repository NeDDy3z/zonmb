<?php

namespace Helpers;

class ReplaceHelper
{
    /**
     * Synthesize the string by replacing special characters with their ASCII equivalent
     * @param string $string
     * @return string
     */
    public static function replaceSpecialChars(string $string): string
    {
        return (string)iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
    }

    /**
     * Convert string to URL friendly string => 'Český text' => 'cesky-text'
     * @param string $string
     * @return string
     */
    public static function getUrlFriendlyString(string $string): string
    {
        return strtolower(self::replaceSpecialChars(str_replace(' ', '-', $string)));
    }
}
