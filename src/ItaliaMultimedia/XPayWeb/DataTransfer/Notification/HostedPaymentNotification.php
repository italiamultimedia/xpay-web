<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Notification;

use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class HostedPaymentNotification implements DataTransferInterface
{
    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $rawData
     */
    public function __construct(
        public readonly string $eventId,
        public readonly ?string $eventTime,
        public readonly string $securityToken,
        public readonly PaymentOperation $operation,
        public readonly array $rawData = [],
    ) {
    }
}
