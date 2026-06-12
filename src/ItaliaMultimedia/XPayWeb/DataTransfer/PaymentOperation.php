<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class PaymentOperation implements DataTransferInterface
{
    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $additionalData
     * @param array<mixed> $rawData
     * @phpcs:enable
     */
    public function __construct(
        public readonly string $orderId,
        public readonly string $operationId,
        public readonly string $operationType,
        public readonly string $operationResult,
        public readonly string $operationTime,
        public readonly ?string $operationAmount = null,
        public readonly ?string $operationCurrency = null,
        public readonly array $additionalData = [],
        public readonly array $rawData = [],
    ) {
    }
}
