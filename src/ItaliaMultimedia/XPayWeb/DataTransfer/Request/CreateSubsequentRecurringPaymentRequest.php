<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class CreateSubsequentRecurringPaymentRequest implements DataTransferInterface
{
    public function __construct(
        public readonly string $correlationId,
        public readonly string $idempotencyKey,
        public readonly string $orderId,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $contractId,
        public readonly ?CreateSubsequentRecurringPaymentOptions $options = null,
    ) {
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     * @phpcs:enable
     */
    public function toArray(): array
    {
        $options = $this->options;

        $data = $this->createData($options);
        $data['order'] = $this->createOrder($options);

        return $data;
    }

    /**
     * @return array<string,string>
     */
    private function createOrder(?CreateSubsequentRecurringPaymentOptions $options): array
    {
        $order = [
            'amount' => (string) $this->amount,
            'currency' => $this->currency,
        ];

        if ($options === null) {
            $order['orderId'] = $this->orderId;

            return $order;
        }

        if ($options->customerId !== null) {
            $order['customerId'] = $options->customerId;
        }

        if ($options->customField !== null) {
            $order['customField'] = $options->customField;
        }

        if ($options->description !== null) {
            $order['description'] = $options->description;
        }

        $order['orderId'] = $this->orderId;

        return $order;
    }

    /**
     * @return array<string,array<string,string>|string>
     */
    private function createData(?CreateSubsequentRecurringPaymentOptions $options): array
    {
        $data = [];

        if ($options !== null && $options->captureType !== null) {
            $data['captureType'] = $options->captureType;
        }

        $data['contractId'] = $this->contractId;

        return $data;
    }
}
