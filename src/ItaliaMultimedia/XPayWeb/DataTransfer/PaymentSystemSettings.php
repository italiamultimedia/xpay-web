<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer;

use UnexpectedValueException;
use WebServCo\Data\Contract\Transfer\DataTransferInterface;

use function in_array;

final class PaymentSystemSettings implements DataTransferInterface
{
    public function __construct(public readonly string $apiKey, public readonly string $environment,)
    {
        $this->validateEnvironment($this->environment);
    }

    private function validateEnvironment(string $environment): bool
    {
        if (!in_array($environment, [Configuration::ENVIRONMENT_TEST, Configuration::ENVIRONMENT_PRODUCTION], true)) {
            throw new UnexpectedValueException('Invalid environment.');
        }

        return true;
    }
}
