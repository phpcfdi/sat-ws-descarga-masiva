<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

class CfdiPackageReader extends AbstractPackageReader
{
    protected function filterEntryFilename(string $filename): bool
    {
        $extension = strval(pathinfo($filename, PATHINFO_EXTENSION));
        if (0 !== strcasecmp('xml', $extension)) {
            return false;
        }
        return true;
    }

    protected function filterContents(string &$contents): bool
    {
        if (false === strpos($contents, '<cfdi:Comprobante')) {
            return false;
        }
        return true;
    }
}
