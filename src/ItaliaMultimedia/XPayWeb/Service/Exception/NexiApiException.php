<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Exception;

use ItaliaMultimedia\XPayWeb\DataTransfer\NexiError;
use RuntimeException;

use function array_map;
use function implode;
use function sprintf;
use function substr;

final class NexiApiException extends RuntimeException
{
    /**
     * @param array<\ItaliaMultimedia\XPayWeb\DataTransfer\NexiError> $errors
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly string $responseBody,
        private readonly array $errors = [],
    ) {
        parent::__construct($this->createMessage($this->statusCode, $this->responseBody, $this->errors));
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): string
    {
        return $this->responseBody;
    }

    /**
     * @return array<\ItaliaMultimedia\XPayWeb\DataTransfer\NexiError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array<\ItaliaMultimedia\XPayWeb\DataTransfer\NexiError> $errors
     */
    private function createMessage(int $statusCode, string $responseBody, array $errors): string
    {
        if ($errors !== []) {
            return sprintf(
                'Nexi API returned HTTP %d: %s',
                $statusCode,
                implode(
                    '; ',
                    array_map(
                        static function (NexiError $error): string {
                            return sprintf('%s %s', $error->code, $error->description);
                        },
                        $errors,
                    ),
                ),
            );
        }

        if ($responseBody !== '') {
            return sprintf('Nexi API returned HTTP %d. Body: %s', $statusCode, substr($responseBody, 0, 500));
        }

        return sprintf('Nexi API returned HTTP %d with empty body.', $statusCode);
    }
}
