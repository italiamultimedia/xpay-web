<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Recurring;

use ItaliaMultimedia\XPayWeb\Contract\Recurring\RecurringPaymentServiceInterface;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateSubsequentRecurringPaymentResponse;
use ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory;
use ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory;
use ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService;
use Override;
use UnexpectedValueException;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function sprintf;

abstract class AbstractRecurringPaymentService extends AbstractPaymentService implements
    RecurringPaymentServiceInterface
{
    public function __construct(
        PaymentSystemSettings $paymentSystemSettings,
        NexiApiExceptionFactory $nexiApiExceptionFactory,
        protected DataExtractionContainerInterface $dataExtractionContainer,
        private PaymentOperationFactory $paymentOperationFactory,
    ) {
        parent::__construct($paymentSystemSettings, $nexiApiExceptionFactory);
    }

    #[Override]
    public function getRecurringPaymentApiUrl(): string
    {
        return sprintf(
            '%s%s',
            $this->getApiBaseUrl(),
            Configuration::RECURRING_PAYMENT_API_ENDPOINT,
        );
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $responseBodyAsArray
     */
    protected function createSubsequentRecurringPaymentResponse(
        array $responseBodyAsArray,
    ): CreateSubsequentRecurringPaymentResponse {
        $operation = $this->getArray($responseBodyAsArray, 'operation');

        return new CreateSubsequentRecurringPaymentResponse(
            $this->paymentOperationFactory->create($operation),
            $responseBodyAsArray,
        );
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $data
     * @return array<mixed>
     * @phpcs:enable
     */
    private function getArray(array $data, string $key): array
    {
        $arrayStorageService = $this->dataExtractionContainer->getArrayStorageService();
        if ($arrayStorageService === null) {
            throw new UnexpectedValueException('Array storage service is not available.');
        }

        return $arrayStorageService->getArray($data, $arrayStorageService->parseKey($key));
    }
}
