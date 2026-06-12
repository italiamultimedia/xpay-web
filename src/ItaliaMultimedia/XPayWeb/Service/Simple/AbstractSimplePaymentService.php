<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Simple;

use ItaliaMultimedia\XPayWeb\Contract\Simple\SimplePaymentServiceInterface;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService;
use Override;

use function sprintf;

abstract class AbstractSimplePaymentService extends AbstractPaymentService implements SimplePaymentServiceInterface
{
    #[Override]
    public function getHostedPaymentPageApiUrl(): string
    {
        return sprintf(
            '%s%s',
            $this->getApiBaseUrl(),
            Configuration::HOSTED_PAYMENT_PAGE_API_ENDPOINT,
        );
    }
}
