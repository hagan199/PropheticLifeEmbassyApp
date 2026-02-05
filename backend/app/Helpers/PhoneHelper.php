<?php

namespace App\Helpers;

class PhoneHelper
{
    /**
     * Format a phone number to Ghana standard (+233)
     *
     * @param string $phone
     * @return string
     */
    public static function format(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with 0, replace with 233
        if (str_starts_with($phone, '0')) {
            $phone = '233' . substr($phone, 1);
        }

        // If doesn't start with 233, add it
        if (!str_starts_with($phone, '233')) {
            $phone = '233' . $phone;
        }

        return '+' . $phone;
    }

    /**
     * Validate Ghana phone number
     *
     * @param string $phone
     * @return bool
     */
    public static function isValid(string $phone): bool
    {
        $formatted = self::format($phone);

        // Ghana phone numbers: +233 followed by 9 digits
        return preg_match('/^\+233[0-9]{9}$/', $formatted) === 1;
    }

    /**
     * Get phone number without country code
     *
     * @param string $phone
     * @return string
     */
    public static function getLocal(string $phone): string
    {
        $formatted = self::format($phone);

        // Remove +233 and return local number
        return '0' . substr($formatted, 4);
    }

    /**
     * Mask phone number for privacy (e.g., +233XXXX4567)
     *
     * @param string $phone
     * @param int $visibleDigits
     * @return string
     */
    public static function mask(string $phone, int $visibleDigits = 4): string
    {
        $formatted = self::format($phone);
        $length = strlen($formatted);
        $maskLength = $length - $visibleDigits - 4; // -4 for +233

        return '+233' . str_repeat('X', $maskLength) . substr($formatted, -$visibleDigits);
    }

    /**
     * Check if two phone numbers are the same
     *
     * @param string $phone1
     * @param string $phone2
     * @return bool
     */
    public static function isSame(string $phone1, string $phone2): bool
    {
        return self::format($phone1) === self::format($phone2);
    }
}
