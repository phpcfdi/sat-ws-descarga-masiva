<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoCfdi;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoRetenciones;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;

final class QueryValidator
{
    /**
     * Return a list of validation errors
     *
     * @return list<string>
     */
    public function validate(QueryParameters $query): array
    {
        if (! $query->getUuid()->isEmpty()) {
            return $this->validateFolio($query);
        }

        return $this->validateQuery($query);
    }

    /** @return list<string> */
    private function validateFolio(QueryParameters $query): array
    {
        $errors = [];

        if (! $query->getRfcMatches()->isEmpty()) {
            $errors[] = 'En una consulta por UUID no se debe usar el filtro de RFC.';
        }
        if (! $query->getComplement()->isUndefined()) {
            $errors[] = 'En una consulta por UUID no se debe usar el filtro de complemento.';
        }
        if (! $query->getDocumentStatus()->isUndefined()) {
            $errors[] = 'En una consulta por UUID no se debe usar el filtro de estado de documento.';
        }
        if (! $query->getDocumentType()->isUndefined()) {
            $errors[] = 'En una consulta por UUID no se debe usar el filtro de tipo de documento.';
        }

        return $errors;
    }

    /** @return list<string> */
    private function validateQuery(QueryParameters $query): array
    {
        $errors = [];

        if ($query->getPeriod()->getStart() >= $query->getPeriod()->getEnd()) {
            $errors[] = sprintf(
                'La fecha de inicio (%s) no puede ser mayor o igual a la fecha final (%s) del periodo de consulta.',
                $query->getPeriod()->getStart()->format('Y-m-d H:i:s'),
                $query->getPeriod()->getEnd()->format('Y-m-d H:i:s'),
            );
        }

        $minimalDate = DateTime::now()->modify('-6 years midnight');
        if ($query->getPeriod()->getStart() < $minimalDate) {
            $errors[] = sprintf(
                'La fecha de inicio (%s) no puede ser menor a hoy menos 6 años atrás (%s).',
                $query->getPeriod()->getStart()->format('Y-m-d H:i:s'),
                $minimalDate->format('Y-m-d H:i:s'),
            );
        }

        if (
            $query->getDownloadType()->isReceived()
            && $query->getRequestType()->isXml()
            && ! $query->getDocumentStatus()->isActive()
        ) {
            $errors[] = sprintf(
                'No es posible hacer una consulta de XML Recibidos que contenga Cancelados. Solicitado: %s.',
                $query->getDocumentStatus()->getQueryAttributeValue()
            );
        }

        if ($query->getDownloadType()->isReceived() && $query->getRfcMatches()->count() > 1) {
            $errors[] = 'No es posible hacer una consulta de Recibidos con más de 1 RFC emisor.';
        }

        if ($query->getDownloadType()->isIssued() && $query->getRfcMatches()->count() > 5) {
            $errors[] = 'No es posible hacer una consulta de Emitidos con más de 5 RFC receptores.';
        }

        if (
            $query->getServiceType()->isCfdi()
            && ! $query->getComplement()->isUndefined()
            && ! $query->getComplement() instanceof ComplementoCfdi
        ) {
            $errors[] = sprintf(
                'El complemento de CFDI definido no es un complemento registrado de este tipo (%s).',
                $query->getComplement()->label()
            );
        }

        if (
            $query->getServiceType()->isRetenciones()
            && ! $query->getComplement()->isUndefined()
            && ! $query->getComplement() instanceof ComplementoRetenciones
        ) {
            $errors[] = sprintf(
                'El complemento de Retenciones definido no es un complemento registrado de este tipo (%s).',
                $query->getComplement()->label()
            );
        }

        return $errors;
    }
}
