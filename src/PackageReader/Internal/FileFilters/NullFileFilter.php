<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters;

/**
 * NullObject patern, it does not filter any file contents
 *
 * @internal
 */
class NullFileFilter implements FileFilterInterface
{
    public function filterFilename(string $filename): bool
    {
        return true;
    }

    public function filterContents(string $contents): bool
    {
        return true;
    }
}
