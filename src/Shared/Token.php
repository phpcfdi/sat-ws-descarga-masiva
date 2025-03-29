<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Defines a Token as given from SAT
 */
final class Token implements JsonSerializable
{
    public function __construct(
        private DateTime $created,
        private DateTime $expires,
        private string $value,
    ) {
        if ($this->expires->compareTo($this->created) < 0) {
            throw new InvalidArgumentException('Cannot create a token with expiration lower than creation');
        }
    }

    /**
     * Token creation date
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * Token expiration date
     *
     * @return DateTime
     */
    public function getExpires(): DateTime
    {
        return $this->expires;
    }

    /**
     * Token value
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * A token is empty if does not contains an internal value
     */
    public function isValueEmpty(): bool
    {
        return '' === $this->value;
    }

    /**
     * A token is expired if the expiration date is greater or equal to current time
     */
    public function isExpired(): bool
    {
        return $this->expires->compareTo(DateTime::now()) < 0;
    }

    /**
     * A token is valid if contains a value and is not expired
     */
    public function isValid(): bool
    {
        return ! $this->isValueEmpty() && ! $this->isExpired();
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'created' => $this->created,
            'expires' => $this->expires,
            'value' => $this->value,
        ];
    }
}
