<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Response;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class CreateHostedPaymentPageResponse implements DataTransferInterface
{
    public function __construct(public readonly string $hostedPage, public readonly string $securityToken,)
    {
    }
}
