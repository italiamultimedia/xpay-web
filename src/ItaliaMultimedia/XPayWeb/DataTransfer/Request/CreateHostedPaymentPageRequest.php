<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class CreateHostedPaymentPageRequest implements DataTransferInterface
{
    public function __construct(
        public readonly string $correlationId,
        public readonly string $orderId,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $language,
        public readonly string $resultUrl,
        public readonly string $cancelUrl,
        public readonly ?CreateHostedPaymentPageOptions $options = null,
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

        return [
            'order' => $this->createOrder($options),
            'paymentSession' => $this->createPaymentSession($options),
        ];
    }

    /**
     * @return array<string,string>
     */
    private function createOrder(?CreateHostedPaymentPageOptions $options): array
    {
        $order = [
            'amount' => (string) $this->amount,
            'currency' => $this->currency,
        ];

        if ($options !== null && $options->description !== null) {
            $order['description'] = $options->description;
        }

        $order['orderId'] = $this->orderId;

        return $order;
    }

    /**
     * @return array<string,array<string,string>|string>
     */
    private function createPaymentSession(?CreateHostedPaymentPageOptions $options): array
    {
        $paymentSession = [
            'actionType' => 'PAY',
            'amount' => (string) $this->amount,
            'cancelUrl' => $this->cancelUrl,
            'language' => $this->language,
        ];

        if ($options === null) {
            $paymentSession['resultUrl'] = $this->resultUrl;

            return $paymentSession;
        }

        $recurrence = $options->recurrence;
        if ($recurrence instanceof HostedPaymentPageRecurrence) {
            $paymentSession['recurrence'] = $recurrence->toArray();
        }

        if ($options->notificationUrl !== null) {
            $paymentSession['notificationUrl'] = $options->notificationUrl;
        }

        $paymentSession['resultUrl'] = $this->resultUrl;

        return $paymentSession;
    }
}
