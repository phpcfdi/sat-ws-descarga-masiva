<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\Actions;

class Argument
{
    /** @var string */
    private $key;

    /** @var string */
    private $alias;

    /** @var string */
    private $info;

    public function __construct(string $key, string $alias, string $info)
    {
        $this->key = $key;
        $this->alias = $alias;
        $this->info = $info;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function matchParameter(string $parameter)
    {
        return ($parameter === '-' . $this->key || $parameter === '--' . $this->alias);
    }

    public function getInfo(): string
    {
        return $this->info;
    }
}
