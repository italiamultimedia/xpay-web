<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Notification;

use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class HostedPaymentNotification implements DataTransferInterface
{
    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $rawData
     */
    public function __construct(
        public string $eventId,
        public ?string $eventTime,
        public string $securityToken,
        public PaymentOperation $operation,
        public array $rawData = [],
    ) {
    }
}
