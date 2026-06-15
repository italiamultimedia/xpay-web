<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransfer\Notification;

use ItaliaMultimedia\XPayWeb\DataTransfer\Notification\HostedPaymentNotification;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use PHPUnit\Framework\TestCase;

final class HostedPaymentNotificationTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Notification\HostedPaymentNotification::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation::__construct
     */
    public function testConstructorStoresNotificationData(): void
    {
        $operation = new PaymentOperation(
            'ORDER-123',
            '3470744',
            'AUTHORIZATION',
            'AUTHORIZED',
            '2022-09-01T01:20:00.001Z',
            '1000',
            'EUR',
            [],
            $this->getOperationPayload(),
        );
        $notification = new HostedPaymentNotification(
            '554ccc00-28fb-4344-a3fa-4bb8d1999bd5',
            '2022-09-01T01:20:00.001Z',
            'security-token',
            $operation,
            $this->getPayload(),
        );

        self::assertSame('554ccc00-28fb-4344-a3fa-4bb8d1999bd5', $notification->eventId);
        self::assertSame('2022-09-01T01:20:00.001Z', $notification->eventTime);
        self::assertSame('security-token', $notification->securityToken);
        self::assertSame('ORDER-123', $notification->operation->orderId);
        self::assertSame('AUTHORIZED', $notification->operation->operationResult);
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     */
    private function getPayload(): array
    {
        return [
            'eventId' => '554ccc00-28fb-4344-a3fa-4bb8d1999bd5',
            'eventTime' => '2022-09-01T01:20:00.001Z',
            'operation' => $this->getOperationPayload(),
            'securityToken' => 'security-token',
        ];
    }

    /**
     * @return array<string,string>
     */
    private function getOperationPayload(): array
    {
        return [
            'channel' => 'ECOMMERCE',
            'channelDetail' => 'HOSTED_PAYMENT_PAGE',
            'operationAmount' => '1000',
            'operationCurrency' => 'EUR',
            'operationId' => '3470744',
            'operationResult' => 'AUTHORIZED',
            'operationTime' => '2022-09-01T01:20:00.001Z',
            'operationType' => 'AUTHORIZATION',
            'orderId' => 'ORDER-123',
            'paymentCircuit' => 'VISA',
            'paymentMethod' => 'CARD',
        ];
    }
}
