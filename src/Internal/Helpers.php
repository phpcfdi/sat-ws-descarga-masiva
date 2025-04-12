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
        return preg_replace(
            [
                '/^\h*/m',      // A: remove horizontal spaces at beginning
                '/\h*\r?\n/m',  // B: remove horizontal spaces + optional CR + LF
                '/\?></',       // C: xml definition on its own line
            ],
            [
                '',             // A: remove
                '',             // B: remove
                "?>\n<",        // C: insert LF
            ],
            $input
        ) ?? '';
    }

    public static function cleanPemContents(string $pemContents): string
    {
        $filteredLines = array_filter(
            explode("\n", $pemContents),
            fn (string $line): bool => ! str_starts_with($line, '-----')
        );
        return implode('', array_map('trim', $filteredLines));
    }
}
