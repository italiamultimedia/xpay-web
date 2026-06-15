<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Recurring;

use Fig\Http\Message\RequestMethodInterface;
use ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateSubsequentRecurringPaymentResponse;
use ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory;
use ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory;
use Override;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function json_encode;
use function strlen;

use const JSON_THROW_ON_ERROR;

final class RecurringPaymentService extends AbstractRecurringPaymentService
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
    public function createSubsequentRecurringPayment(
        CreateSubsequentRecurringPaymentRequest $createSubsequentRecurringPaymentRequest,
    ): CreateSubsequentRecurringPaymentResponse {
        $request = $this->createRequest($createSubsequentRecurringPaymentRequest);

        $this->response = $this->httpDependencyContainer->httpClient->sendRequest($request);

        $this->validateResponseStatusCode($this->response, 200);

        $responseBodyAsArray = $this->getResponseBodyAsArray($this->response);

        return $this->createSubsequentRecurringPaymentResponse($responseBodyAsArray);
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    private function createRequest(
        CreateSubsequentRecurringPaymentRequest $createSubsequentRecurringPaymentRequest,
    ): RequestInterface {
        $requestBody = json_encode($createSubsequentRecurringPaymentRequest->toArray(), JSON_THROW_ON_ERROR);

        $request = $this->httpDependencyContainer->requestFactory->createRequest(
            RequestMethodInterface::METHOD_POST,
            $this->getRecurringPaymentApiUrl(),
        );

        $request = $request->withBody($this->httpDependencyContainer->streamFactory->createStream($requestBody));

        $correlationId = $createSubsequentRecurringPaymentRequest->correlationId;

        foreach ($this->getRequestHeaders($correlationId) as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request
            ->withHeader('Idempotency-Key', $createSubsequentRecurringPaymentRequest->idempotencyKey)
            ->withHeader('Content-Length', (string) strlen($requestBody))
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Encoding', '');
    }
}
