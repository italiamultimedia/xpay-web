<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class CreateSubsequentRecurringPaymentOptions implements DataTransferInterface
{
    public const string CAPTURE_TYPE_EXPLICIT = 'EXPLICIT';

    public const string CAPTURE_TYPE_IMPLICIT = 'IMPLICIT';

    public function __construct(
        public ?string $captureType = null,
        public ?string $customerId = null,
        public ?string $description = null,
        public ?string $customField = null,
    ) {
    }
}
