<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Contract\Simple;

use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateHostedPaymentPageResponse;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse;

interface SimplePaymentServiceInterface
{
    public function createHostedPaymentPage(
        CreateHostedPaymentPageRequest $createHostedPaymentPageRequest,
    ): CreateHostedPaymentPageResponse;

    public function getHostedPaymentPageApiUrl(): string;

    public function getOrderApiUrl(string $orderId): string;

    public function retrieveOrderStatus(
        RetrieveOrderStatusRequest $retrieveOrderStatusRequest,
    ): RetrieveOrderStatusResponse;
}
