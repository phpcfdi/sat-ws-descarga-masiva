<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

class Helpers
{
    public static function createUuid(): string
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
        return implode(
            '',
            array_map(
                'trim',
                array_filter(
                    explode("\n", $pemContents),
                    function (string $line): bool {
                        return (0 !== strpos($line, '-----'));
                    }
                )
            )
        );
    }
}
