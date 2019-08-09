<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use BadMethodCallException;

/**
 * @method bool isUnknown()
 * @internal
 */
abstract class OpenEnum
{
    /**
     * Array where each entry contains a name and message
     */
    protected const VALUES = [];

    /**
     * Public message of state Unknown
     */
    protected const UNKNOWN_MESSAGE = '(valor del estado desconocido)';

    /** @var int */
    private $value;

    /** @var string */
    private $name;

    /** @var string */
    private $message;

    public function __construct(int $value)
    {
        $this->value = $value;
        $this->name = static::VALUES[$this->value]['name'] ?? 'Unknown';
        $this->message = static::VALUES[$this->value]['message'] ?? static::UNKNOWN_MESSAGE;
    }

    public function __call($name, $arguments)
    {
        if (0 === strpos(strtolower($name), 'is') && strlen($name) > 2) {
            return (0 === strcasecmp(substr($name, 2), $this->name));
        }
        throw new BadMethodCallException(sprintf('Call to undefined method %s::%s', get_class($this), $name));
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
