<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

class StatusCode
{
    /** @var int */
    private $code;

    /** @var string */
    private $message;

    public function __construct(int $code, string $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isAccepted(): bool
    {
        return (5000 === $this->code);
    }
}
