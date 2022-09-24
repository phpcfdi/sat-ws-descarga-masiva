<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters;

/**
 * Implementation to filter a Metadata Package file contents
 *
 * @internal
 */
class ThirdPartiesFileFilter implements FileFilterInterface
{
    public function filterFilename(string $filename): bool
    {
        return boolval(preg_match('/^[^\/\\\\]+_tercero\.txt/i', $filename));
    }

    public function filterContents(string $contents): bool
    {
        return ('Uuid~RfcACuentaTerceros~NombreACuentaTerceros' === substr($contents, 0, 45));
    }
}
