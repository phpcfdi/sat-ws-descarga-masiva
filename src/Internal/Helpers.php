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
    public static function createXmlSecurityTokenId(): string
    {
        $md5 = md5(uniqid());
        return sprintf(
            'uuid-%08s-%04s-%04s-%04s-%012s-1',
            substr($md5, 0, 8),
            substr($md5, 8, 4),
            substr($md5, 12, 4),
            substr($md5, 16, 4),
            substr($md5, 20)
        );
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
