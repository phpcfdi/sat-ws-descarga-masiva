<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva;

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
    private Token $token;

    private readonly ServiceEndpoints $endpoints;

    /**
     * Client constructor of "servicio de consulta y recuperación de comprobantes"
     *
     * @param ServiceEndpoints|null $endpoints If NULL uses CFDI endpoints
     */
    public function __construct(
        private readonly RequestBuilderInterface $requestBuilder,
        private readonly WebClientInterface $webclient,
        ?Token $token = null,
        ?ServiceEndpoints $endpoints = null,
    ) {
        $this->token = $token ?? Token::empty();
        $this->endpoints = $endpoints ?? ServiceEndpoints::cfdi();
    }

    /**
     * This method will reuse the current token,
     * it will create a new one if there is none or the current token is no longer valid
     */
    public function obtainCurrentToken(): Token
    {
        if (! $this->token->isValid()) {
            $this->token = $this->authenticate();
        }
        return $this->token;
    }

    public function getToken(): Token
    {
        return $this->token;
    }

    public function setToken(Token $token): void
    {
        $this->token = $token;
    }

    public function getEndpoints(): ServiceEndpoints
    {
        return $this->endpoints;
    }

    /**
     * Perform authentication and return a Token, the token might be invalid
     */
    public function authenticate(): Token
    {
        $authenticateTranslator = new AuthenticateTranslator();
        $soapBody = $authenticateTranslator->createSoapRequest($this->requestBuilder);
        $responseBody = $this->consume(
            'http://DescargaMasivaTerceros.gob.mx/IAutenticacion/Autentica',
            $this->endpoints->getAuthenticate(),
            $soapBody,
            null, // do not use a token
        );
        return $authenticateTranslator->createTokenFromSoapResponse($responseBody);
    }

    /**
     * Consume the "SolicitaDescarga" web service
     */
    public function query(QueryParameters $parameters): QueryResult
    {
        if (! $this->endpoints->getServiceType()->equalTo($parameters->getServiceType())) {
            $parameters = $parameters->withServiceType($this->endpoints->getServiceType());
        }
        $queryTranslator = new QueryTranslator();
        $soapBody = $queryTranslator->createSoapRequest($this->requestBuilder, $parameters);
        $soapAction = $this->resolveSoapAction($parameters);
        $responseBody = $this->consume(
            "http://DescargaMasivaTerceros.sat.gob.mx/ISolicitaDescargaService/$soapAction",
            $this->endpoints->getQuery(),
            $soapBody,
            $this->obtainCurrentToken()
        );
        return $queryTranslator->createQueryResultFromSoapResponse($responseBody);
    }

    /**
     * Consume the "VerificaSolicitudDescarga" web service
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

    private function resolveSoapAction(QueryParameters $parameters): string
    {
        if (! $parameters->getUuid()->isEmpty()) {
            return 'SolicitaDescargaFolio';
        }

        return $parameters->getDownloadType()->isReceived() ? 'SolicitaDescargaRecibidos' : 'SolicitaDescargaEmitidos';
    }

    private function consume(string $soapAction, string $uri, string $body, ?Token $token): string
    {
        return ServiceConsumer::consume($this->webclient, $soapAction, $uri, $body, $token);
    }
}
