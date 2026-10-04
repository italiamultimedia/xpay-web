<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Response;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class RetrieveOrderStatusResponse implements DataTransferInterface
{
    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<\ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation> $operations
     * @param array<mixed> $rawData
     * @phpcs:enable
     */
    public function __construct(
        public string $orderId,
        public string $orderAmount,
        public string $orderCurrency,
        public ?string $authorizedAmount = null,
        public ?string $capturedAmount = null,
        public ?string $lastOperationType = null,
        public ?string $lastOperationTime = null,
        public array $operations = [],
        public array $rawData = [],
    ) {
    }
}
