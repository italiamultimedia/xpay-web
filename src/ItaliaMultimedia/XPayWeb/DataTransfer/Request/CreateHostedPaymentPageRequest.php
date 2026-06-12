<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final class CreateHostedPaymentPageRequest implements DataTransferInterface
{
    public function __construct(
        public readonly string $orderId,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $language,
        public readonly string $resultUrl,
        public readonly string $cancelUrl,
        public readonly ?string $notificationUrl = null,
        public readonly ?string $description = null,
    ) {
    }

    /**
     * @return array<string,array<string,string>>
     */
    public function toArray(): array
    {
        $data = [
            'order' => [
                'amount' => (string) $this->amount,
                'currency' => $this->currency,
                'orderId' => $this->orderId,
            ],
            'paymentSession' => [
                'actionType' => 'PAY',
                'amount' => (string) $this->amount,
                'cancelUrl' => $this->cancelUrl,
                'language' => $this->language,
                'resultUrl' => $this->resultUrl,
            ],
        ];

        if ($this->notificationUrl !== null) {
            $data['paymentSession']['notificationUrl'] = $this->notificationUrl;
        }

        if ($this->description !== null) {
            $data['order']['description'] = $this->description;
        }

        return $data;
    }
}
