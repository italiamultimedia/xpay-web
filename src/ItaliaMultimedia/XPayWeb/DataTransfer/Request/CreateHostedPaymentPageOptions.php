<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class CreateHostedPaymentPageOptions implements DataTransferInterface
{
    public function __construct(
        public ?string $notificationUrl = null,
        public ?string $description = null,
        public ?HostedPaymentPageRecurrence $recurrence = null,
    ) {
    }
}
