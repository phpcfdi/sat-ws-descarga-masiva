<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\PackageReader;

/**
 * Metadata DTO object
 * @internal This struct is reported as of 2019-08-01, if changes use all()/get() methods
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
 */
class MetadataItem
{
    /** @var string[] */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function get(string $key): string
    {
        return $this->data[$key] ?? '';
    }
}
