<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Internal;

/**
 * Helper functions used by the library.
 *
 * This class is internal, do not use it outside this project
 * @internal
 */
class Helpers
{
    public static function nospaces(string $input): string
    {
        return preg_replace(['/^\h*/m', '/\h*\r?\n/m'], '', $input) ?? '';
    }

    public static function cleanPemContents(string $pemContents): string
    {
        $filteredLines = array_filter(
            explode("\n", $pemContents),
            function (string $line): bool {
                return (0 !== strpos($line, '-----'));
            }
        );
        return implode('', array_map('trim', $filteredLines));
    }
}
