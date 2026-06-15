<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service;

use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

abstract class AbstractPaymentService
{
    public function __construct(
        protected PaymentSystemSettings $paymentSystemSettings,
        private NexiApiExceptionFactory $nexiApiExceptionFactory,
    ) {
    }

    /**
     * @return array<string,string>
     */
    protected function getRequestHeaders(string $correlationId): array
    {
        return [
            'Content-Type' => 'application/json',
            'Correlation-Id' => $correlationId,
            'X-API-KEY' => $this->paymentSystemSettings->apiKey,
        ];
    }

    protected function getApiBaseUrl(): string
    {
        return match ($this->paymentSystemSettings->environment) {
            Configuration::ENVIRONMENT_TEST => Configuration::API_URL_TEST,
            Configuration::ENVIRONMENT_PRODUCTION => Configuration::API_URL_PRODUCTION,
            default => throw new UnexpectedValueException('Unhandled environment.'),
        };
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     */
    protected function getResponseBodyAsArray(ResponseInterface $response): array
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

    protected function validateResponseStatusCode(ResponseInterface $response, int $expectedStatusCode): bool
    {
        if ($response->getStatusCode() !== $expectedStatusCode) {
            throw $this->nexiApiExceptionFactory->create($response);
        }

        return true;
    }
}
