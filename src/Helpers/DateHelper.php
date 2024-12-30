<?php

namespace Helpers;

use DateTime;
use Exception;

/**
 * DateHelper
 *
 * This helper class provides methods for converting dates between different formats,
 * specifically ISO format (`Y-m-d`) and a more human-readable "pretty" format (`d.m.Y`).
 * It also includes utilities for validating and transforming dates.
 *
 * @package Helpers
 * @author Erik Vaněk
 */
class DateHelper
{
    /**
     * The format used for human-readable "pretty" dates.
     */
    private const PRETTY_FORMAT = 'd.m.Y';

    /**
     * The format used for ISO-standard dates.
     */
    private const ISO_FORMAT = 'Y-m-d';

    /**
     * Convert a date from ISO format (`Y-m-d`) or a `DateTime` object to the pretty format (`d.m.Y`).
     *
     * If a valid date string or `DateTime` object is provided, it will be converted to the
     * pretty format. If the conversion fails, it returns "N/A".
     *
     * @param string|DateTime|null $date The input date in ISO format, as a `DateTime` object, or `null`.
     *
     * @return string The date in pretty format or "N/A" if conversion fails.
     */
    public static function toPrettyFormat(string|DateTime|null $date): string
    {
        try {
            // Convert date based on type
            return match (true) {
                $date instanceof DateTime => (string)$date->format(self::PRETTY_FORMAT),
                default => date(self::PRETTY_FORMAT, strtotime($date)),
            };
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Convert a date from pretty format (`d.m.Y`) or a `DateTime` object to ISO format (`Y-m-d`).
     *
     * This method attempts to parse the input date and format it in the ISO standard.
     * If parsing or conversion fails, it returns an empty string.
     *
     * @param string|DateTime|null $date The input date in pretty format, as a `DateTime` object, or `null`.
     *
     * @return string The date in ISO format or an empty string if conversion fails.
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
     * Validate and convert a pretty format date (`d.m.Y`) to ISO format (`Y-m-d`) if applicable.
     *
     * This method checks if the provided date is in the pretty format (`d.m.Y`) and,
     * if valid, converts it to ISO format. If the date isn't in pretty format or is `null`,
     * it returns the original value.
     *
     * @param string|null $date The input date in pretty format or `null`.
     *
     * @return string|null The date in ISO format if converted, or the original value.
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
