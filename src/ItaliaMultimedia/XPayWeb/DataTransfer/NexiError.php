<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class NexiError implements DataTransferInterface
{
    public function __construct(public readonly string $code, public readonly string $description,)
    {
    }
}
