<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

use JsonSerializable;

/**
 * Metadata DTO object
 *
 * @internal This collection of magic properties is reported as of 2019-08-01, if it changes use all()/get() methods
 *
 * @property-read string $uuid
 * @property-read string $rfcEmisor
 * @property-read string $nombreEmisor
 * @property-read string $rfcReceptor
 * @property-read string $nombreReceptor
 * @property-read string $rfcPac
 * @property-read string $fechaEmision
 * @property-read string $fechaCertificacionSat
 * @property-read string $monto
 * @property-read string $efectoComprobante
 * @property-read string $estatus
 * @property-read string $fechaCancelacion
 * @property-read string $rfcACuentaTerceros
 * @property-read string $nombreACuentaTerceros
 */
final class MetadataItem implements JsonSerializable
{
    /** @param array<string, string> $data */
    public function __construct(private array $data)
    {
    }

    public function __get(string $name): string
    {
        return $this->get($name);
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return $this->data;
    }

    public function get(string $key): string
    {
        return $this->data[$key] ?? '';
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return ['uuid' => $this->get('uuid')] + $this->data;
    }
}
