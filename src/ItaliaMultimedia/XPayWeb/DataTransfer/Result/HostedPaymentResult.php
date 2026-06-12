<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Result;

use UnexpectedValueException;
use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class HostedPaymentResult implements DataTransferInterface
{
    public function __construct(public readonly string $orderId, public readonly ?string $paymentId = null,)
    {
        if ($this->orderId === '') {
            throw new UnexpectedValueException('Missing or invalid "orderId" data.');
        }

        if ($this->paymentId === '') {
            throw new UnexpectedValueException('Missing or invalid "paymentId" data.');
        }
    }
}
