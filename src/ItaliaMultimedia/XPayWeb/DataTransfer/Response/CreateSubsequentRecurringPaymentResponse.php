<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Response;

use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class CreateSubsequentRecurringPaymentResponse implements DataTransferInterface
{
    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $rawData
     * @phpcs:enable
     */
    public function __construct(public readonly PaymentOperation $operation, public readonly array $rawData = [],)
    {
    }
}
