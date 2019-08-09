<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use InvalidArgumentException;

class Token
{
    /** @var DateTime */
    private $created;

    /** @var DateTime */
    private $expires;

    /** @var string */
    private $value;

    public function __construct(DateTime $created, DateTime $expires, string $value)
    {
        if ($expires->compareTo($created) < 0) {
            throw new InvalidArgumentException('Cannot create a token with expiration lower than creation');
        }
        $this->created = $created;
        $this->expires = $expires;
        $this->value = $value;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getExpires(): DateTime
    {
        return $this->expires;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isValueEmpty(): bool
    {
        return ('' === $this->value);
    }

    public function isExpired(): bool
    {
        return $this->expires->compareTo(DateTime::now()) < 0;
    }

    public function isValid(): bool
    {
        return ! ($this->isValueEmpty() || $this->isExpired());
    }
}
