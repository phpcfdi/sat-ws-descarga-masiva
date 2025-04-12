<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Shared;

use Eclipxe\MicroCatalog\MicroCatalog;
use JsonSerializable;

/**
 * Defines "EstadoSolicitud"
 *
 * @method bool isAccepted()
 * @method bool isInProgress()
 * @method bool isFinished()
 * @method bool isFailure()
 * @method bool isRejected()
 * @method bool isExpired()
 *
 * @method string getMessage() Contains the known message in spanish
 * @method string getName() Contains the internal name
 *
 * @extends MicroCatalog<array{name: string, message: string}>
 */
final class StatusRequest extends MicroCatalog implements JsonSerializable
{
    protected const VALUES = [
        1 => ['name' => 'Accepted', 'message' => 'Aceptada'],
        2 => ['name' => 'InProgress', 'message' => 'En proceso'],
        3 => ['name' => 'Finished', 'message' => 'Terminada'],
        4 => ['name' => 'Failure', 'message' => 'Error'],
        5 => ['name' => 'Rejected', 'message' => 'Rechazada'],
        6 => ['name' => 'Expired', 'message' => 'Vencida'],
    ];

    public static function getEntriesArray(): array
    {
        return self::VALUES;
    }

    public function getEntryValueOnUndefined()
    {
        return ['name' => 'Unknown', 'message' => 'Desconocida'];
    }

    public function getEntryId(): string
    {
        return $this->getName();
    }

    /**
     * Contains the "EstadoSolicitud" value
     */
    public function getValue(): int
    {
        return intval($this->getEntryIndex());
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return [
            'value' => $this->getValue(),
            'message' => $this->getMessage(),
        ];
    }
}
