<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Contract\Recurring;

use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateSubsequentRecurringPaymentResponse;

interface RecurringPaymentServiceInterface
{
    public function createSubsequentRecurringPayment(
        CreateSubsequentRecurringPaymentRequest $createSubsequentRecurringPaymentRequest,
    ): CreateSubsequentRecurringPaymentResponse;

    public function getRecurringPaymentApiUrl(): string;
}
