<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use JsonSerializable;

/**
 * Defines "CodEstatus" and "Mensaje"
 */
final class StatusCode implements JsonSerializable
{
    public function __construct(private readonly int $code, private readonly string $message)
    {
    }

    /**
     * Contains the value of "CodEstatus"
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * Contains the value of "Mensaje"
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Return true when "CodEstatus" is success
     * The only success code is "5000: Solicitud recibida con éxito"
     */
    public function isAccepted(): bool
    {
        return 5000 === $this->code;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
}
