<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class RetrieveOrderStatusRequest implements DataTransferInterface
{
    public function __construct(public readonly string $correlationId, public readonly string $orderId,)
    {
    }
}
