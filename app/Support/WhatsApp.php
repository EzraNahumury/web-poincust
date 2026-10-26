<?php

namespace App\Support;

/**
 * WhatsApp number normalisation + manual chat-link building.
 *
 * There is NO WhatsApp API / bot / automated sending here (README §13.3).
 * We only normalise the stored number and build a wa.me deep link that an
 * admin opens and sends manually.
 */
class WhatsApp
{
    /**
     * Normalise an Indonesian phone number to international digits, e.g.
     * "0812-3456-7890" / "+62 812 3456 7890" => "6281234567890".
     * Returns null if nothing usable remains.
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        // Keep digits only.
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '62')) {
            // already international
        } elseif (str_starts_with($digits, '8')) {
            // local mobile without leading zero
            $digits = '62' . $digits;
        }

        return $digits;
    }

    /** A plausible Indonesian mobile number in 62… form. */
    public static function isValid(?string $normalized): bool
    {
        if ($normalized === null) {
            return false;
        }

        return (bool) preg_match('/^62\d{8,13}$/', $normalized);
    }

    /** Build a wa.me deep link with a correctly URL-encoded prefilled message. */
    public static function link(string $normalizedNumber, string $message = ''): string
    {
        $url = 'https://wa.me/' . $normalizedNumber;
        if ($message !== '') {
            $url .= '?text=' . rawurlencode($message);
        }

        return $url;
    }
}
