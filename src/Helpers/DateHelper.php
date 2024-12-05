<?php

namespace Helpers;

use DateTime;
use Exception;

class DateHelper
{
    private const DATE_FORMAT = 'd.m.Y';

    /**
     * Convert computer date '2024-12-03' to human format '12.3.2024'
     * @param string|DateTime|null $date
     * @return string
     */
    public static function dateTopPrettyString(string|DateTime|null $date): string
    {
        try {
            $date = match (true) {
                $date instanceof DateTime => $date,
                is_string($date) => new DateTime($date),
                default => new DateTime(),
            };

            return $date->format(self::DATE_FORMAT);
        } catch (Exception $e) {
            return 'N/A';
        }

    }
}
