<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

interface ActionInterface
{
    public function run(string ...$parameters): void;

    public function help(): void;
}
