<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI;

class Printer
{
    /** @var string */
    public $stdout;

    /** @var string */
    public $stderr;

    public function __construct(string $stdout = 'php://stdout', string $stderr = 'php://stderr')
    {
        $this->stdout = $stdout;
        $this->stderr = $stderr;
    }

    public function stdout(...$lines): void
    {
        $this->print($this->stdout, ...$lines);
    }

    public function stderr(...$lines): void
    {
        $this->print($this->stderr, ...$lines);
    }

    private function print(string $where, ...$lines): void
    {
        foreach ($lines as $line) {
            file_put_contents($where, $line . PHP_EOL, FILE_APPEND);
        }
    }
}
