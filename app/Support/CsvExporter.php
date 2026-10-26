<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a CSV download. Every cell is guarded against CSV/formula injection
 * (README §14): values beginning with = + - @ or control chars are prefixed so
 * spreadsheet apps never execute them as formulas.
 */
class CsvExporter
{
    public static function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel reads accents/rupiah correctly.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, array_map([self::class, 'sanitize'], $headers));
            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'sanitize'], $row));
            }
            fclose($out);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    public static function sanitize(mixed $value): string
    {
        $value = (string) $value;

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
