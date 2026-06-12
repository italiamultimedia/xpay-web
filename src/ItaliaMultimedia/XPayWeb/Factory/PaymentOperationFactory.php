<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Factory;

use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use UnexpectedValueException;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function is_array;
use function preg_match;
use function sprintf;

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
            $this->getIso8601String($data, 'operationTime'),
            $dataExtractionService->getNullableString($data, 'operationAmount'),
            $dataExtractionService->getNullableString($data, 'operationCurrency'),
            $this->getArray($data, 'additionalData'),
            $data,
        );
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $data
     */
    private function getIso8601String(array $data, string $key): string
    {
        $value = $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService()
            ->getNonEmptyString($data, $key);

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/', $value) !== 1) {
            throw new UnexpectedValueException(sprintf('Invalid "%s" ISO 8601 data.', $key));
        }

        return $value;
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
