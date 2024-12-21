<?php

namespace Helpers;

use DateTime;
use Exception;

class DateHelper
{
    private const PRETTY_FORMAT = 'd.m.Y';
    private const ISO_FORMAT = 'Y-m-d';

    /**
     * Convert ISO date '2024-12-03' to human format '12.3.2024'
     * @param string|DateTime|null $date
     * @return string
     */
    public static function toPrettyFormat(string|DateTime|null $date): string
    {
        try {
            return match (true) {
                $date instanceof DateTime => (string)$date->format(self::PRETTY_FORMAT),
                default => date(self::PRETTY_FORMAT, strtotime($date)),
            };
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Convert pretty date '12.21.2024' to ISO format '2024-12-21'
     * @param string|DateTime|null $date
     * @return string
     */
    public static function toISOFormat(string|DateTime|null $date): string
    {
        try {
            return date(self::ISO_FORMAT, strtotime($date));
        } catch (Exception $e) {
            return '';
        }
    }


    /**
     * If date is in pretty format convert it to ISO format
     * @param string|null $date
     * @return string|null
     */
    public static function ifPrettyConvertToISO(string|null $date): string|null
    {
        if (isset($date) && preg_match("/^(?:0?[1-9]|[12][0-9]|3[01])\.(?:0?[1-9]|1[0-2])\.\d{4}$/", $date)) {
            return self::toISOFormat($date);
        } else {
            return $date;
        }
    }
}
