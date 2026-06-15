<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransfer\Request;

use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentOptions;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest;
use PHPUnit\Framework\TestCase;

final class CreateSubsequentRecurringPaymentRequestTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest::__construct
     */
    public function testToArrayReturnsRequiredSubsequentRecurringPaymentPayload(): void
    {
        $request = new CreateSubsequentRecurringPaymentRequest(
            '2f0ea505-9b41-414a-b374-4fe672327d85',
            'f4c1a3fd-63f7-4f19-b229-dcc8d0ca5923',
            'ORDER-124',
            1000,
            'EUR',
            'CONTRACT-123',
        );

        self::assertSame(
            [
                'contractId' => 'CONTRACT-123',
                'order' => [
                    'amount' => '1000',
                    'currency' => 'EUR',
                    'orderId' => 'ORDER-124',
                ],
            ],
            $request->toArray(),
        );
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentOptions::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest::__construct
     */
    public function testToArrayReturnsOptionalSubsequentRecurringPaymentPayload(): void
    {
        $request = new CreateSubsequentRecurringPaymentRequest(
            '2f0ea505-9b41-414a-b374-4fe672327d85',
            'f4c1a3fd-63f7-4f19-b229-dcc8d0ca5923',
            'ORDER-124',
            1000,
            'EUR',
            'CONTRACT-123',
            new CreateSubsequentRecurringPaymentOptions(
                CreateSubsequentRecurringPaymentOptions::CAPTURE_TYPE_IMPLICIT,
                'CUSTOMER-123',
                'Subscription renewal',
                'Plan A',
            ),
        );

        self::assertSame($this->getOptionalPayload(), $request->toArray());
    }

    /**
     * @return array<string,array<string,string>|string>
     */
    private function getOptionalPayload(): array
    {
        return [
            'captureType' => CreateSubsequentRecurringPaymentOptions::CAPTURE_TYPE_IMPLICIT,
            'contractId' => 'CONTRACT-123',
            'order' => [
                'amount' => '1000',
                'currency' => 'EUR',
                'customerId' => 'CUSTOMER-123',
                'customField' => 'Plan A',
                'description' => 'Subscription renewal',
                'orderId' => 'ORDER-124',
            ],
        ];
    }
}
