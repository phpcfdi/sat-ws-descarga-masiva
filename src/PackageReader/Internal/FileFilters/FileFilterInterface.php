<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal\FileFilters;

/**
 * Filter by filename or content contract
 */
interface FileFilterInterface
{
    /**
     * Filter the file name
     */
    public function filterFilename(string $filename): bool;

    /**
     * Filter the contents
     */
    public function filterContents(string $contents): bool;
}
