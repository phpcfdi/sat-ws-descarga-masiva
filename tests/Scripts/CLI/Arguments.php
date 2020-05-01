<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Scripts\CLI;

class Arguments
{
    /** @var Argument[] */
    public $arguments;

    public function __construct(Argument ...$arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param string[] $parameters
     * @return array<string, string[]>
     */
    public function parseParameters(array $parameters): array
    {
        /** @var string[] $matches */
        $matches = [];
        /** @var string[] $unmatched */
        $unmatched = [];
        $length = count($parameters);
        for ($i = 0; $i < $length; $i = $i + 1) {
            $parameter = $parameters[$i];
            $argument = $this->findArgumentByParameter($parameter);
            if (null !== $argument) {
                $matches[$argument->getKey()] = $parameters[$i + 1] ?? '';
                $i = $i + 1;
            } else {
                $unmatched[] = $parameter;
            }
        }
        return ['matched' => $matches, 'unmatched' => $unmatched];
    }

    public function findArgumentByParameter(string $parameter): ?Argument
    {
        foreach ($this->arguments as $argument) {
            if ($argument->matchParameter($parameter)) {
                return $argument;
            }
        }
        return null;
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        return array_map(
            function (Argument $argument) {
                return sprintf('  -%s, --%s: %s', $argument->getKey(), $argument->getAlias(), $argument->getInfo());
            },
            $this->arguments
        );
    }
}
