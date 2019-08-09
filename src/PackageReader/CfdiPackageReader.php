<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

class CfdiPackageReader extends AbstractPackageReader
{
    protected function filterEntryFilename(string $filename): bool
    {
        if (boolval(preg_match('/^[\w\-]{36}\.xml$/i', $filename))) {
            return true;
        }
        return false;
    }

    protected function filterContents(string &$contents): bool
    {
        if (false === strpos($contents, '<cfdi:Comprobante')) {
            return false;
        }
        return true;
    }
}
