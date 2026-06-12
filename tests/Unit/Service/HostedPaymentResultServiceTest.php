<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use WebServCo\Data\Factory\Extraction\DataExtractionContainerFactory;

final class HostedPaymentResultServiceTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::createHostedPaymentResult
     */
    public function testCreateHostedPaymentResultReturnsResult(): void
    {
        $service = $this->createService();

        $result = $service->createHostedPaymentResult('ORDER-123');

        self::assertSame('ORDER-123', $result->orderId);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::parseHostedPaymentNotification
     */
    public function testParseHostedPaymentNotificationReturnsNotification(): void
    {
        $service = $this->createService();

        $notification = $service->parseHostedPaymentNotification($this->getPayload(), 'security-token');

        self::assertSame('ORDER-123', $notification->operation->orderId);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::parseHostedPaymentNotification
     */
    public function testParseHostedPaymentNotificationRejectsInvalidSecurityToken(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid hosted payment notification security token.');

        $service = $this->createService();

        $notification = $service->parseHostedPaymentNotification($this->getPayload(), 'different-token');
        self::fail($notification::class . ' should not have been created.');
    }

    private function createService(): HostedPaymentResultService
    {
        return new HostedPaymentResultService(
            (new DataExtractionContainerFactory())->createDataExtractionContainer(true),
        );
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
            'operation' => [
                'operationId' => '3470744',
                'operationResult' => 'AUTHORIZED',
                'operationTime' => '2022-09-01T01:20:00.001Z',
                'operationType' => 'AUTHORIZATION',
                'orderId' => 'ORDER-123',
            ],
            'securityToken' => 'security-token',
        ];
    }
}
