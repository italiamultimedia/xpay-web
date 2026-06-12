<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransfer\Request;

use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use PHPUnit\Framework\TestCase;

final class CreateHostedPaymentPageRequestTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest::toArray
     */
    public function testToArrayReturnsRequiredHostedPaymentPagePayload(): void
    {
        $request = new CreateHostedPaymentPageRequest(
            '2f0ea505-9b41-414a-b374-4fe672327d85',
            'ORDER-123',
            1000,
            'EUR',
            'ENG',
            'https://example.com/payment/result',
            'https://example.com/payment/cancel',
        );

        self::assertSame($this->getRequiredPayload(), $request->toArray());
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest::toArray
     */
    public function testToArrayReturnsOptionalHostedPaymentPagePayload(): void
    {
        $request = new CreateHostedPaymentPageRequest(
            '2f0ea505-9b41-414a-b374-4fe672327d85',
            'ORDER-123',
            1000,
            'EUR',
            'ENG',
            'https://example.com/payment/result',
            'https://example.com/payment/cancel',
            'https://example.com/payment/notification',
            'Test order',
        );

        self::assertSame($this->getOptionalPayload(), $request->toArray());
    }

    /**
     * @return array<string,array<string,string>>
     */
    private function getRequiredPayload(): array
    {
        return [
            'order' => [
                'amount' => '1000',
                'currency' => 'EUR',
                'orderId' => 'ORDER-123',
            ],
            'paymentSession' => [
                'actionType' => 'PAY',
                'amount' => '1000',
                'cancelUrl' => 'https://example.com/payment/cancel',
                'language' => 'ENG',
                'resultUrl' => 'https://example.com/payment/result',
            ],
        ];
    }

    /**
     * @return array<string,array<string,string>>
     */
    private function getOptionalPayload(): array
    {
        return [
            'order' => [
                'amount' => '1000',
                'currency' => 'EUR',
                'description' => 'Test order',
                'orderId' => 'ORDER-123',
            ],
            'paymentSession' => [
                'actionType' => 'PAY',
                'amount' => '1000',
                'cancelUrl' => 'https://example.com/payment/cancel',
                'language' => 'ENG',
                'notificationUrl' => 'https://example.com/payment/notification',
                'resultUrl' => 'https://example.com/payment/result',
            ],
        ];
    }
}
