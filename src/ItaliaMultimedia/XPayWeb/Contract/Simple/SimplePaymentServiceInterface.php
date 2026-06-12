<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Contract\Simple;

use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateHostedPaymentPageResponse;

interface SimplePaymentServiceInterface
{
    public function createHostedPaymentPage(
        CreateHostedPaymentPageRequest $createHostedPaymentPageRequest,
    ): CreateHostedPaymentPageResponse;

    public function getHostedPaymentPageApiUrl(): string;
}
