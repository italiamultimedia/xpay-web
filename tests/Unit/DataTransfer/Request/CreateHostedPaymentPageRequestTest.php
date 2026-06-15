<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransfer\Request;

use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageOptions;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence;
use PHPUnit\Framework\TestCase;

final class CreateHostedPaymentPageRequestTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest::__construct
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
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageOptions::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest::__construct
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
            new CreateHostedPaymentPageOptions('https://example.com/payment/notification', 'Test order'),
        );

        self::assertSame($this->getOptionalPayload(), $request->toArray());
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageOptions::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence::toArray
     */
    public function testToArrayReturnsRecurringHostedPaymentPagePayload(): void
    {
        $request = new CreateHostedPaymentPageRequest(
            '2f0ea505-9b41-414a-b374-4fe672327d85',
            'ORDER-123',
            1000,
            'EUR',
            'ENG',
            'https://example.com/payment/result',
            'https://example.com/payment/cancel',
            new CreateHostedPaymentPageOptions(
                null,
                'Test order',
                new HostedPaymentPageRecurrence(
                    'CONTRACT-123',
                    HostedPaymentPageRecurrence::CONTRACT_TYPE_MIT_SCHEDULED,
                    '2027-12-31',
                    '30',
                ),
            ),
        );

        self::assertSame($this->getRecurringPayload(), $request->toArray());
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

    /**
     * @return array<string,array<string,array<string,string>|string>>
     */
    private function getRecurringPayload(): array
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
                'recurrence' => [
                    'action' => HostedPaymentPageRecurrence::ACTION_CONTRACT_CREATION,
                    'contractExpiryDate' => '2027-12-31',
                    'contractFrequency' => '30',
                    'contractId' => 'CONTRACT-123',
                    'contractType' => HostedPaymentPageRecurrence::CONTRACT_TYPE_MIT_SCHEDULED,
                ],
                'resultUrl' => 'https://example.com/payment/result',
            ],
        ];
    }
}
