<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Contract;

use ItaliaMultimedia\XPayWeb\DataTransfer\Notification\HostedPaymentNotification;
use ItaliaMultimedia\XPayWeb\DataTransfer\Result\HostedPaymentResult;

interface HostedPaymentResultServiceInterface
{
    public function createHostedPaymentResult(string $orderId): HostedPaymentResult;

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $data
     */
    public function parseHostedPaymentNotification(
        array $data,
        string $expectedSecurityToken,
    ): HostedPaymentNotification;
}
