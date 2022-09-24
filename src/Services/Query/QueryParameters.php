<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Services\Query;

use JsonSerializable;
use LogicException;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoInterface;
use PhpCfdi\SatWsDescargaMasiva\Shared\ComplementoUndefined;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTimePeriod;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentStatus;
use PhpCfdi\SatWsDescargaMasiva\Shared\DocumentType;
use PhpCfdi\SatWsDescargaMasiva\Shared\DownloadType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RequestType;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatch;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcMatches;
use PhpCfdi\SatWsDescargaMasiva\Shared\RfcOnBehalf;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceType;
use PhpCfdi\SatWsDescargaMasiva\Shared\Uuid;

/**
 * This class contains all the information required to perform a query on the SAT Web Service
 */
final class QueryParameters implements JsonSerializable
{
    /** @var ?ServiceType */
    private $serviceType = null;

    /** @var DateTimePeriod */
    private $period;

    /** @var DownloadType */
    private $downloadType;

    /** @var RequestType */
    private $requestType;

    /** @var DocumentType */
    private $documentType;

    /** @var ComplementoInterface */
    private $complement;

    /** @var DocumentStatus */
    private $documentStatus;

    /** @var Uuid */
    private $uuid;

    /** @var RfcOnBehalf */
    private $rfcOnBehalf;

    /** @var RfcMatches */
    private $rfcMatches;

    private function __construct(
        DateTimePeriod $period,
        DownloadType $downloadType,
        RequestType $requestType,
        DocumentType $documentType,
        ComplementoInterface $complement,
        DocumentStatus $documentStatus,
        Uuid $uuid,
        RfcOnBehalf $rfcOnBehalf,
        RfcMatches $rfcMatches
    ) {
        $this->period = $period;
        $this->downloadType = $downloadType;
        $this->requestType = $requestType;
        $this->documentType = $documentType;
        $this->complement = $complement;
        $this->documentStatus = $documentStatus;
        $this->uuid = $uuid;
        $this->rfcOnBehalf = $rfcOnBehalf;
        $this->rfcMatches = $rfcMatches;
    }

    /**
     * Query static constructor method
     *
     * @param DateTimePeriod|null $period
     * @param DownloadType|null $downloadType
     * @param RequestType|null $requestType
     * @return self
     */
    public static function create(
        ?DateTimePeriod $period = null,
        ?DownloadType $downloadType = null,
        ?RequestType $requestType = null
    ): self {
        return new self(
            $period ?? DateTimePeriod::createFromValues($currentTime = time(), $currentTime),
            $downloadType ?? DownloadType::issued(),
            $requestType ?? RequestType::metadata(),
            DocumentType::undefined(),
            ComplementoUndefined::undefined(),
            DocumentStatus::undefined(),
            Uuid::empty(),
            RfcOnBehalf::empty(),
            RfcMatches::create()
        );
    }

    public function hasServiceType(): bool
    {
        return (null !== $this->serviceType);
    }

    public function getServiceType(): ServiceType
    {
        if (null === $this->serviceType) {
            throw new LogicException('Service type has not been set');
        }
        return $this->serviceType;
    }

    public function getPeriod(): DateTimePeriod
    {
        return $this->period;
    }

    public function getDownloadType(): DownloadType
    {
        return $this->downloadType;
    }

    public function getRequestType(): RequestType
    {
        return $this->requestType;
    }

    public function getDocumentType(): DocumentType
    {
        return $this->documentType;
    }

    public function getComplement(): ComplementoInterface
    {
        return $this->complement;
    }

    public function getDocumentStatus(): DocumentStatus
    {
        return $this->documentStatus;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getRfcOnBehalf(): RfcOnBehalf
    {
        return $this->rfcOnBehalf;
    }

    public function getRfcMatches(): RfcMatches
    {
        return $this->rfcMatches;
    }

    public function getRfcMatch(): RfcMatch
    {
        return $this->rfcMatches->getFirst();
    }

    public function withServiceType(ServiceType $serviceType): self
    {
        return $this->with('serviceType', $serviceType);
    }

    public function withPeriod(DateTimePeriod $period): self
    {
        return $this->with('period', $period);
    }

    public function withDownloadType(DownloadType $downloadType): self
    {
        return $this->with('downloadType', $downloadType);
    }

    public function withRequestType(RequestType $requestType): self
    {
        return $this->with('requestType', $requestType);
    }

    public function withDocumentType(DocumentType $documentType): self
    {
        return $this->with('documentType', $documentType);
    }

    public function withComplement(ComplementoInterface $complement): self
    {
        return $this->with('complement', $complement);
    }

    public function withDocumentStatus(DocumentStatus $documentStatus): self
    {
        return $this->with('documentStatus', $documentStatus);
    }

    public function withUuid(Uuid $uuid): self
    {
        return $this->with('uuid', $uuid);
    }

    public function withRfcOnBehalf(RfcOnBehalf $rfcOnBehalf): self
    {
        return $this->with('rfcOnBehalf', $rfcOnBehalf);
    }

    public function withRfcMatches(RfcMatches $rfcMatches): self
    {
        return $this->with('rfcMatches', $rfcMatches);
    }

    public function withRfcMatch(RfcMatch $rfcMatch): self
    {
        return $this->with('rfcMatches', RfcMatches::create($rfcMatch));
    }

    /** @param mixed $value */
    private function with(string $property, $value): self
    {
        $clone = clone $this;
        $clone->{$property} = $value;
        return $clone;
    }

    /** @return array<string, mixed> */
    public function jsonSerialize(): array
    {
        return array_filter([
            'serviceType' => $this->serviceType,
            'period' => $this->period,
            'downloadType' => $this->downloadType,
            'requestType' => $this->requestType,
            'documentType' => $this->documentType,
            'complement' => $this->complement,
            'documentStatus' => $this->documentStatus,
            'uuid' => $this->uuid,
            'rfcOnBehalf' => $this->rfcOnBehalf,
            'rfcMatches' => $this->rfcMatches,
        ]);
    }
}
