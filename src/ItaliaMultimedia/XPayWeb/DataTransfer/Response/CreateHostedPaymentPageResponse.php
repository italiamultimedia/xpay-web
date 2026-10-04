<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Response;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class CreateHostedPaymentPageResponse implements DataTransferInterface
{
    public function __construct(public string $hostedPage, public string $securityToken,)
    {
    }
}
