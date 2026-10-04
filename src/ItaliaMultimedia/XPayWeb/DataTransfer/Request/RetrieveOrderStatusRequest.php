<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class RetrieveOrderStatusRequest implements DataTransferInterface
{
    public function __construct(public string $correlationId, public string $orderId,)
    {
    }
}
