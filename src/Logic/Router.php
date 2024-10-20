<?php
declare(strict_types=1);

namespace Zonmb\Logic;

use JetBrains\PhpStorm\NoReturn;

class Router
{
    /**
     * @param string $path
     * @param string|null $query
     * @param string|null $parameters
     * @param int $responseCode
     * @return void
     */
    public static function redirect(string $path, ?string $query = null, ?string $parameters = null, int $responseCode = 200): void
    {
        $resultQuery = '';
        if ($query && $parameters) {
            $resultQuery = '?'. $query .'='. urlencode($parameters);
        }

        http_response_code($responseCode);
        header(header: ('location: ./'. $path . $resultQuery));
        exit();
    }
}