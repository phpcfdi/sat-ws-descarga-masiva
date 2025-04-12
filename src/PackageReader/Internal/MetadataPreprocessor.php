<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader\Internal;

/**
 * This preprocesor fixes metadata issues:
 * - SAT CSV EOL is <CR><LF> and might contain <LF> inside a field
 *
 * @see MetadataContent
 * @internal
 */
final class MetadataPreprocessor
{
    private const CONTROL_CR = "\r";

    private const CONTROL_LF = "\n";

    private const CONTROL_CRLF = "\r\n";

    /**
     * @param string $contents The data to process
     */
    public function __construct(private string $contents)
    {
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function fix(): void
    {
        // all the fixes should exist here
        $this->fixEolCrLf();
    }

    public function fixEolCrLf(): void
    {
        // check if EOL is <CR><LF>
        $firstLineFeedPosition = strpos($this->contents, self::CONTROL_LF);
        if (false === $firstLineFeedPosition) {
            $eolIsCrLf = false;
        } else {
            $eolIsCrLf = (self::CONTROL_CR === substr($this->contents, $firstLineFeedPosition - 1, 1));
        }

        // exit early if nothing to do
        if (! $eolIsCrLf) {
            return;
        }

        // repair inner <LF>, EOL must be <CR><LF>
        $lines = explode(self::CONTROL_CRLF, $this->contents);
        foreach ($lines as &$line) {
            $line = str_replace(self::CONTROL_LF, '', $line);
        }
        $this->contents = implode(self::CONTROL_LF, $lines);
    }
}
