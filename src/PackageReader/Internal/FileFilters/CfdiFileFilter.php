<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters;

use PhpCfdi\SatWsDescargaMasiva\PackageReader\CfdiPackageReader;

/**
 * Implementation to filter a CFDI Package file contents
 *
 * @internal
 */
final class CfdiFileFilter implements FileFilterInterface
{
    public function filterFilename(string $filename): bool
    {
        return boolval(preg_match('/^[^\/\\\\]+\.xml/i', $filename));
    }

    public function filterContents(string $contents): bool
    {
        return '' !== CfdiPackageReader::obtainUuidFromXmlCfdi($contents);
    }
}
