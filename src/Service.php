<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

use LogicException;
use PhpCfdi\SatWsDescargaMasiva\Internal\ServiceConsumer;
use PhpCfdi\SatWsDescargaMasiva\RequestBuilder\RequestBuilderInterface;
use PhpCfdi\SatWsDescargaMasiva\Services\Authenticate\AuthenticateTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Download\DownloadTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryParameters;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Query\QueryTranslator;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyResult;
use PhpCfdi\SatWsDescargaMasiva\Services\Verify\VerifyTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\ServiceEndpoints;
use PhpCfdi\SatWsDescargaMasiva\Shared\Token;
use PhpCfdi\SatWsDescargaMasiva\WebClient\WebClientInterface;

/**
 * Main class to consume the SAT web service Descarga Masiva
 */
class Service
{
    /** @var RequestBuilderInterface */
    private $requestBuilder;

    /** @var WebClientInterface */
    private $webclient;

    /** @var Token|null */
    public $currentToken;

    /** @var ServiceEndpoints */
    private $endpoints;

    /**
     * Client constructor of "servicio de consulta y recuperación de comprobantes"
     *
     * @param RequestBuilderInterface $requestBuilder
     * @param WebClientInterface $webclient
     * @param Token|null $currentToken
     * @param ServiceEndpoints|null $endpoints If NULL uses CFDI endpoints
     */
    public function __construct(
        RequestBuilderInterface $requestBuilder,
        WebClientInterface $webclient,
        Token $currentToken = null,
        ServiceEndpoints $endpoints = null
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->webclient = $webclient;
        $this->currentToken = $currentToken;
        $this->endpoints = $endpoints ?? ServiceEndpoints::cfdi();
    }

    /**
     * This method will reuse the current token,
     * it will create a new one if there is none or the current token is no longer valid
     *
     * @return Token
     */
    public function obtainCurrentToken(): Token
    {
        if (null === $this->currentToken || ! $this->currentToken->isValid()) {
            $this->currentToken = $this->authenticate();
        }
        return $this->currentToken;
    }

    /**
     * Perform authentication and return a Token, the token might be invalid
     *
     * @return Token
     */
    public function authenticate(): Token
    {
        $authenticateTranslator = new AuthenticateTranslator();
        $soapBody = $authenticateTranslator->createSoapRequest($this->requestBuilder);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.gob.mx/IAutenticacion/Autentica',
            $this->endpoints->getAuthenticate(),
            $soapBody
        );
        return $authenticateTranslator->createTokenFromSoapResponse($responseBody);
    }

    /**
     * Consume the "SolicitaDescarga" web service
     *
     * @param QueryParameters $parameters
     * @return QueryResult
     */
    public function query(QueryParameters $parameters): QueryResult
    {
        // fix parameters service type
        if (! $parameters->hasServiceType()) {
            $parameters = $parameters->withServiceType($this->endpoints->getServiceType());
        }
        if (! $this->endpoints->getServiceType()->equalTo($parameters->getServiceType())) {
            throw new LogicException(
                sprintf(
                    'The service type endpoints [%s] does not match with the service type query [%s]',
                    $parameters->getServiceType()->value(),
                    $this->endpoints->getServiceType()->value()
                )
            );
        }
        $queryTranslator = new QueryTranslator();
        $soapBody = $queryTranslator->createSoapRequest($this->requestBuilder, $parameters);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/ISolicitaDescargaService/SolicitaDescarga',
            $this->endpoints->getQuery(),
            $soapBody,
            $this->obtainCurrentToken()
        );
        return $queryTranslator->createQueryResultFromSoapResponse($responseBody);
    }

    /**
     * Consume the "VerificaSolicitudDescarga" web service
     *
     * @param string $requestId
     * @return VerifyResult
     */
    public function verify(string $requestId): VerifyResult
    {
        $verifyTranslator = new VerifyTranslator();
        $soapBody = $verifyTranslator->createSoapRequest($this->requestBuilder, $requestId);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/IVerificaSolicitudDescargaService/VerificaSolicitudDescarga',
            $this->endpoints->getVerify(),
            $soapBody,
            $this->obtainCurrentToken()
        );
        return $verifyTranslator->createVerifyResultFromSoapResponse($responseBody);
    }

    /**
     * Consume the "Descargar" web service
     *
     * @param string $packageId
     * @return DownloadResult
     */
    public function download(string $packageId): DownloadResult
    {
        $downloadTranslator = new DownloadTranslator();
        $soapBody = $downloadTranslator->createSoapRequest($this->requestBuilder, $packageId);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.sat.gob.mx/IDescargaMasivaTercerosService/Descargar',
            $this->endpoints->getDownload(),
            $soapBody,
            $this->obtainCurrentToken()
        );
        return $downloadTranslator->createDownloadResultFromSoapResponse($responseBody);
    }

    private function consume(string $soapAction, string $uri, string $body, ?Token $token = null): string
    {
        return ServiceConsumer::consume($this->webclient, $soapAction, $uri, $body, $token);
    }
}
