<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Factory;

use ItaliaMultimedia\XPayWeb\DataTransfer\NexiError;
use ItaliaMultimedia\XPayWeb\Exception\NexiApiException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;

use function is_array;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class NexiApiExceptionFactory
{
    public function __construct(private DataExtractionContainerInterface $dataExtractionContainer)
    {
    }

    public function create(ResponseInterface $response): NexiApiException
    {
        $responseBody = (string) $response->getBody();

        return new NexiApiException(
            $response->getStatusCode(),
            $responseBody,
            $this->createErrors($responseBody),
        );
    }

    /**
     * @return array<\ItaliaMultimedia\XPayWeb\DataTransfer\NexiError>
     */
    private function createErrors(string $responseBody): array
    {
        $data = $this->decodeResponseBody($responseBody);
        $errorData = $data['errors'] ?? null;
        if (!is_array($errorData)) {
            return [];
        }

        return $this->createErrorsFromArray($errorData);
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     * @phpcs:enable
     */
    private function decodeResponseBody(string $responseBody): array
    {
        if ($responseBody === '') {
            return [];
        }

        try {
            $data = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (!is_array($data)) {
            return [];
        }

        return $data;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $errorData
     * @return array<\ItaliaMultimedia\XPayWeb\DataTransfer\NexiError>
     * @phpcs:enable
     */
    private function createErrorsFromArray(array $errorData): array
    {
        $nonEmptyDataExtractionService = $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService();
        $errors = [];
        foreach ($errorData as $error) {
            if (!is_array($error)) {
                continue;
            }

            $errors[] = new NexiError(
                $nonEmptyDataExtractionService->getNonEmptyString($error, 'code'),
                $nonEmptyDataExtractionService->getNonEmptyString($error, 'description'),
            );
        }

        return $errors;
    }
}
