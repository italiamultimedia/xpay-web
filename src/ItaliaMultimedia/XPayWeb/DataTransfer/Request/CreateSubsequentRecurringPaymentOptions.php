<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class CreateSubsequentRecurringPaymentOptions implements DataTransferInterface
{
    public const string CAPTURE_TYPE_EXPLICIT = 'EXPLICIT';

    public const string CAPTURE_TYPE_IMPLICIT = 'IMPLICIT';

    public function __construct(
        public readonly ?string $captureType = null,
        public readonly ?string $customerId = null,
        public readonly ?string $description = null,
        public readonly ?string $customField = null,
    ) {
    }
}
