<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Factory;

use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function is_array;

final class PaymentOperationFactory
{
    public function __construct(private DataExtractionContainerInterface $dataExtractionContainer)
    {
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $data
     * @phpcs:enable
     */
    public function create(array $data): PaymentOperation
    {
        $dataExtractionService = $this->dataExtractionContainer->getLooseArrayDataExtractionService();
        $nonEmptyDataExtractionService = $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService();

        return new PaymentOperation(
            $nonEmptyDataExtractionService->getNonEmptyString($data, 'orderId'),
            $nonEmptyDataExtractionService->getNonEmptyString($data, 'operationId'),
            $nonEmptyDataExtractionService->getNonEmptyString($data, 'operationType'),
            $nonEmptyDataExtractionService->getNonEmptyString($data, 'operationResult'),
            $nonEmptyDataExtractionService->getNonEmptyString($data, 'operationTime'),
            $dataExtractionService->getNullableString($data, 'operationAmount'),
            $dataExtractionService->getNullableString($data, 'operationCurrency'),
            $this->getArray($data, 'additionalData'),
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
