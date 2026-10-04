<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class NexiError implements DataTransferInterface
{
    public function __construct(public string $code, public string $description,)
    {
    }
}
