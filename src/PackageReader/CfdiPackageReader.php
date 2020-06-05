<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

class CfdiPackageReader extends AbstractPackageReader
{
    protected function filterEntryFilename(string $filename): bool
    {
        // this regexp means that start with al least 1 char that is not "/" or "\"
        // and continues and ends with ".xml". So x.xml x.xml.xml are valid, but not a/x.xml
        if (boolval(preg_match('/^[^\/\\\\]+\.xml$/i', $filename))) {
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
