<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Error\NexiErrorMessage;

use ItaliaMultimedia\XPayWeb\Enum\NexiErrorCode;

final class EnglishNexiErrorMessageProvider
{
    public function getCustomerMessage(?NexiErrorCode $errorCode): string
    {
        return match ($errorCode) {
            NexiErrorCode::DUPLICATE_ORDER => 'This payment request already exists. Please refresh and try again.',
            NexiErrorCode::ORDER_NOT_FOUND,
            NexiErrorCode::ORDER_OPERATION_NOT_FOUND,
            NexiErrorCode::TRANSACTION_NOT_FOUND => 'The payment could not be found. Please contact support.',
            NexiErrorCode::SERVICE_TEMPORARILY_UNAVAILABLE => 'The payment service is temporarily unavailable. '
                . 'Please try again later.',
            NexiErrorCode::GENERIC_API_ERROR,
            NexiErrorCode::INTERNAL_ERROR,
            NexiErrorCode::PAYMENT_INTERNAL_ERROR,
            NexiErrorCode::PAYMENT_SETUP_INTERNAL_ERROR,
            NexiErrorCode::PAYMENT_VALIDATION_INTERNAL_ERROR => 'The payment service had a temporary problem. '
                . 'Please try again.',
            default => 'The payment could not be completed. Please try again or contact support.',
        };
    }
}
