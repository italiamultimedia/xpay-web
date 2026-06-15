<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Simple;

use Fig\Http\Message\RequestMethodInterface;
use ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateHostedPaymentPageResponse;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse;
use ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory;
use ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function json_encode;
use function strlen;

use const JSON_THROW_ON_ERROR;

final class SimplePaymentService extends AbstractSimplePaymentService
{
    private ?ResponseInterface $response = null;

    public function __construct(
        private HttpDependencyContainer $httpDependencyContainer,
        PaymentSystemSettings $paymentSystemSettings,
        NexiApiExceptionFactory $nexiApiExceptionFactory,
        DataExtractionContainerInterface $dataExtractionContainer,
        PaymentOperationFactory $paymentOperationFactory,
    ) {
        parent::__construct(
            $paymentSystemSettings,
            $nexiApiExceptionFactory,
            $dataExtractionContainer,
            $paymentOperationFactory,
        );
    }

    #[Override]
    public function createHostedPaymentPage(
        CreateHostedPaymentPageRequest $createHostedPaymentPageRequest,
    ): CreateHostedPaymentPageResponse {
        $request = $this->createHostedPaymentPageRequest($createHostedPaymentPageRequest);

        $this->response = $this->httpDependencyContainer->httpClient->sendRequest($request);

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

        $this->response = $this->httpDependencyContainer->httpClient->sendRequest($request);

        $this->validateResponseStatusCode($this->response, 200);

        $responseBodyAsArray = $this->getResponseBodyAsArray($this->response);

        return $this->createRetrieveOrderStatusResponse($responseBodyAsArray);
    }

    private function createHostedPaymentPageRequest(
        CreateHostedPaymentPageRequest $createHostedPaymentPageRequest,
    ): RequestInterface {
        $requestBody = json_encode($createHostedPaymentPageRequest->toArray(), JSON_THROW_ON_ERROR);

        $request = $this->httpDependencyContainer->requestFactory->createRequest(
            RequestMethodInterface::METHOD_POST,
            $this->getHostedPaymentPageApiUrl(),
        );

        $request = $request->withBody($this->httpDependencyContainer->streamFactory->createStream($requestBody));

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
        $request = $this->httpDependencyContainer->requestFactory->createRequest(
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
