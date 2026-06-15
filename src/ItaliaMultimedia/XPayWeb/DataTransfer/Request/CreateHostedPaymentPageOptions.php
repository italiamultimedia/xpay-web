<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class CreateHostedPaymentPageOptions implements DataTransferInterface
{
    public function __construct(
        public readonly ?string $notificationUrl = null,
        public readonly ?string $description = null,
        public readonly ?HostedPaymentPageRecurrence $recurrence = null,
    ) {
    }
}
