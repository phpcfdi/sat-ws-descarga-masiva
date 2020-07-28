<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

/**
 * This preprocesor fixes metadata common issues:
 * - SAT CSV EOL is <CR><LF> and might contain <LF> inside a field
 * - Quotes do not delimitate fields, they are unscaped
 *
 * @internal
 */
class MetadataPreprocessor
{
    private const CONTROL_CR = "\r";

    private const CONTROL_LF = "\n";

    private const CONTROL_CRLF = "\r\n";

    /** @var string The data to process */
    private $contents;

    /** @var bool Define if the EOL contains <CR><LF> or only <LF> */
    private $eolHasCr;

    public function __construct(string $contents)
    {
        $this->contents = $contents;
        $firstLineFeedPosition = strpos($contents, self::CONTROL_LF);
        if (false === $firstLineFeedPosition) {
            $this->eolHasCr = false;
        } else {
            $this->eolHasCr = (self::CONTROL_CR === substr($contents, $firstLineFeedPosition - 1, 1));
        }
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    public function fix(): void
    {
        $this->fixQuotes();
        if ($this->eolHasCr) {
            $this->fixInnerLineFeed();
        }
    }

    private function fixQuotes(): void
    {
        // A single quote " should be scaped to three quotes """
        $this->contents = str_replace('"', '"""', $this->contents);
    }

    private function fixInnerLineFeed(): void
    {
        // repair inner <LF>, EOL must be <CR><LF>
        $lines = explode(self::CONTROL_CRLF, $this->contents);
        foreach ($lines as &$line) {
            $line = str_replace(self::CONTROL_LF, '', $line);
        }
        $this->contents = implode(self::CONTROL_LF, $lines);
    }
}
