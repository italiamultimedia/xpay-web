<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class PaymentOperation implements DataTransferInterface
{
    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $additionalData
     * @param array<mixed> $rawData
     * @phpcs:enable
     */
    public function __construct(
        public string $orderId,
        public string $operationId,
        public string $operationType,
        public string $operationResult,
        public ?string $operationTime = null,
        public ?string $operationAmount = null,
        public ?string $operationCurrency = null,
        public array $additionalData = [],
        public array $rawData = [],
    ) {
    }
}
