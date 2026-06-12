<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Simple;

use Fig\Http\Message\RequestMethodInterface;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateHostedPaymentPageResponse;
use Override;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use UnexpectedValueException;

use function is_array;
use function is_string;
use function json_decode;
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
    ) {
        parent::__construct($paymentSystemSettings);
    }

    #[Override]
    public function createHostedPaymentPage(
        CreateHostedPaymentPageRequest $createHostedPaymentPageRequest,
    ): CreateHostedPaymentPageResponse {
        $request = $this->createRequest($createHostedPaymentPageRequest);

        $this->response = $this->httpClient->sendRequest($request);

        $this->validateResponseStatusCode($this->response);

        $responseBodyAsArray = $this->getResponseBodyAsArray($this->response);

        return $this->createResponse($responseBodyAsArray);
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    private function createRequest(CreateHostedPaymentPageRequest $createHostedPaymentPageRequest): RequestInterface
    {
        $requestBody = json_encode($createHostedPaymentPageRequest->toArray(), JSON_THROW_ON_ERROR);

        $request = $this->requestFactory->createRequest(
            RequestMethodInterface::METHOD_POST,
            $this->getHostedPaymentPageApiUrl(),
        );

        $request = $request->withBody($this->streamFactory->createStream($requestBody));

        foreach ($this->getRequestHeaders() as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        return $request
            ->withHeader('Content-Length', (string) strlen($requestBody))
            ->withHeader('Accept', 'application/json')
            ->withHeader('Accept-Encoding', '');
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     */
    private function getResponseBodyAsArray(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        if ($body === '') {
            throw new UnexpectedValueException('Response body is empty.');
        }

        $array = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($array)) {
            throw new UnexpectedValueException('Error decoding JSON data.');
        }

        return $array;
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $responseBodyAsArray
     */
    private function createResponse(array $responseBodyAsArray): CreateHostedPaymentPageResponse
    {
        $hostedPage = $responseBodyAsArray['hostedPage'] ?? null;
        $securityToken = $responseBodyAsArray['securityToken'] ?? null;

        if (!is_string($hostedPage) || $hostedPage === '') {
            throw new UnexpectedValueException('Invalid hosted page response.');
        }

        if (!is_string($securityToken) || $securityToken === '') {
            throw new UnexpectedValueException('Invalid security token response.');
        }

        return new CreateHostedPaymentPageResponse($hostedPage, $securityToken);
    }

    private function validateResponseStatusCode(ResponseInterface $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            throw new UnexpectedValueException('Response does not contain 200 status code.');
        }

        return true;
    }
}
