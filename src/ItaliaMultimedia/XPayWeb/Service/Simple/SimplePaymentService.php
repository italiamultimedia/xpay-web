<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Simple;

use Fig\Http\Message\RequestMethodInterface;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateHostedPaymentPageResponse;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse;
use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function json_encode;
use function strlen;

use const JSON_THROW_ON_ERROR;

final class SimplePaymentService extends AbstractSimplePaymentService
{
    private ?ResponseInterface $response = null;

    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        PaymentSystemSettings $paymentSystemSettings,
        DataExtractionContainerInterface $dataExtractionContainer,
    ) {
        parent::__construct($paymentSystemSettings, $dataExtractionContainer);
    }

    #[Override]
    public function createHostedPaymentPage(
        CreateHostedPaymentPageRequest $createHostedPaymentPageRequest,
    ): CreateHostedPaymentPageResponse {
        $request = $this->createHostedPaymentPageRequest($createHostedPaymentPageRequest);

        $this->response = $this->httpClient->sendRequest($request);

        $this->validateResponseStatusCode($this->response, 200);

        $responseBodyAsArray = $this->getResponseBodyAsArray($this->response);

        return $this->createHostedPaymentPageResponse($responseBodyAsArray);
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    #[Override]
    public function retrieveOrderStatus(
        RetrieveOrderStatusRequest $retrieveOrderStatusRequest,
    ): RetrieveOrderStatusResponse {
        $request = $this->createRetrieveOrderStatusRequest($retrieveOrderStatusRequest);

        $this->response = $this->httpClient->sendRequest($request);

        $this->validateResponseStatusCode($this->response, 200);

        $responseBodyAsArray = $this->getResponseBodyAsArray($this->response);

        return $this->createRetrieveOrderStatusResponse($responseBodyAsArray);
    }

    private function createHostedPaymentPageRequest(
        CreateHostedPaymentPageRequest $createHostedPaymentPageRequest,
    ): RequestInterface {
        $requestBody = json_encode($createHostedPaymentPageRequest->toArray(), JSON_THROW_ON_ERROR);

        $request = $this->requestFactory->createRequest(
            RequestMethodInterface::METHOD_POST,
            $this->getHostedPaymentPageApiUrl(),
        );

        $request = $request->withBody($this->streamFactory->createStream($requestBody));

        $correlationId = $createHostedPaymentPageRequest->correlationId;

        foreach (
            $this->getRequestHeaders($correlationId) as $headerName => $headerValue
        ) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request
            ->withHeader('Content-Length', (string) strlen($requestBody))
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Encoding', '');
    }

    private function createRetrieveOrderStatusRequest(
        RetrieveOrderStatusRequest $retrieveOrderStatusRequest,
    ): RequestInterface {
        $request = $this->requestFactory->createRequest(
            RequestMethodInterface::METHOD_GET,
            $this->getOrderApiUrl($retrieveOrderStatusRequest->orderId),
        );

        foreach (
            $this->getRequestHeaders($retrieveOrderStatusRequest->correlationId) as $headerName => $headerValue
        ) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Encoding', '');
    }
}
