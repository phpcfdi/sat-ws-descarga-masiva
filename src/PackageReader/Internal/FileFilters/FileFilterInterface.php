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
     *
     * @param string $filename
     * @return bool
     */
    public function filterFilename(string $filename): bool;

    /**
     * Filter the contents
     *
     * @param string $contents
     * @return bool
     */
    public function filterContents(string $contents): bool;
}
