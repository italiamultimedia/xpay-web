<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service;

use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use UnexpectedValueException;

abstract class AbstractPaymentService
{
    public function __construct(protected PaymentSystemSettings $paymentSystemSettings)
    {
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
}
