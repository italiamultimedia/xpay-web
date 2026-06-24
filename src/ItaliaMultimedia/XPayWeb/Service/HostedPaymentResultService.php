<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service;

use ItaliaMultimedia\XPayWeb\Contract\HostedPaymentResultServiceInterface;
use ItaliaMultimedia\XPayWeb\DataTransfer\Notification\HostedPaymentNotification;
use ItaliaMultimedia\XPayWeb\DataTransfer\Result\HostedPaymentResult;
use ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory;
use Override;
use UnexpectedValueException;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function hash_equals;
use function is_array;

final class HostedPaymentResultService implements HostedPaymentResultServiceInterface
{
    /**
     * The resultUrl is created by the merchant and should already be tied to the local orderId.
     * Nexi may append paymentId/paymentid as redirect metadata, but payment verification
     * must be performed through GET /orders/{orderId}.
     */
    public function __construct(
        private DataExtractionContainerInterface $dataExtractionContainer,
        private PaymentOperationFactory $paymentOperationFactory,
    ) {
    }

    #[Override]
    public function createHostedPaymentResult(string $orderId): HostedPaymentResult
    {
        return new HostedPaymentResult($orderId);
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $data
     */
    #[Override]
    public function parseHostedPaymentNotification(
        array $data,
        string $expectedSecurityToken,
    ): HostedPaymentNotification {
        $dataExtractionService = $this->dataExtractionContainer->getLooseArrayDataExtractionService();
        $nonEmptyDataExtractionService = $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService();
        $securityToken = $nonEmptyDataExtractionService->getNonEmptyString($data, 'securityToken');
        if (!hash_equals($expectedSecurityToken, $securityToken)) {
            throw new UnexpectedValueException('Invalid hosted payment notification security token.');
        }

        return new HostedPaymentNotification(
            $nonEmptyDataExtractionService->getNonEmptyString($data, 'eventId'),
            $dataExtractionService->getNullableString($data, 'eventTime'),
            $securityToken,
            $this->paymentOperationFactory->create($this->getArray($data, 'operation')),
            $data,
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
        if ($arrayStorageService !== null) {
            return $arrayStorageService->getArray($data, $arrayStorageService->parseKey($key));
        }

        $value = $data[$key] ?? [];
        if (!is_array($value)) {
            return [];
        }

        return $value;
    }
}
