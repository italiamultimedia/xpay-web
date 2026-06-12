<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Error;

use ItaliaMultimedia\XPayWeb\DataTransfer\NexiError;
use ItaliaMultimedia\XPayWeb\Enum\LanguageCode;
use ItaliaMultimedia\XPayWeb\Enum\NexiErrorCode;
use ItaliaMultimedia\XPayWeb\Service\Error\NexiErrorMessage\EnglishNexiErrorMessageProvider;
use ItaliaMultimedia\XPayWeb\Service\Error\NexiErrorMessage\ItalianNexiErrorMessageProvider;
use ItaliaMultimedia\XPayWeb\Service\Exception\NexiApiException;

final class NexiErrorMessageService
{
    public function __construct(
        private EnglishNexiErrorMessageProvider $englishMessageProvider = new EnglishNexiErrorMessageProvider(),
        private ItalianNexiErrorMessageProvider $italianMessageProvider = new ItalianNexiErrorMessageProvider(),
    ) {
    }

    public function getCustomerMessage(NexiApiException $nexiApiException, string $languageCode): string
    {
        $errorCode = $this->getFirstErrorCode($nexiApiException);

        return match ($languageCode) {
            LanguageCode::ITALIAN->value => $this->italianMessageProvider->getCustomerMessage($errorCode),
            default => $this->englishMessageProvider->getCustomerMessage($errorCode),
        };
    }

    public function getTechnicalMessage(NexiApiException $nexiApiException): string
    {
        return $nexiApiException->getMessage();
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    private function getFirstErrorCode(NexiApiException $nexiApiException,): ?NexiErrorCode
    {
        $error = $this->getFirstError($nexiApiException);

        return $error !== null
            ? NexiErrorCode::tryFrom($error->code)
            : null;
    }

    private function getFirstError(NexiApiException $nexiApiException): ?NexiError
    {
        $errors = $nexiApiException->getErrors();
        $error = $errors[0] ?? null;

        return $error instanceof NexiError
            ? $error
            : null;
    }
}
