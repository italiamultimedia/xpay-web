<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Response;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class RetrieveOrderStatusResponse implements DataTransferInterface
{
    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<\ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation> $operations
     * @param array<mixed> $rawData
     * @phpcs:enable
     */
    public function __construct(
        public readonly string $orderId,
        public readonly string $orderAmount,
        public readonly string $orderCurrency,
        public readonly ?string $authorizedAmount = null,
        public readonly ?string $capturedAmount = null,
        public readonly ?string $lastOperationType = null,
        public readonly ?string $lastOperationTime = null,
        public readonly array $operations = [],
        public readonly array $rawData = [],
    ) {
    }
}
