<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory;
use ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use WebServCo\Data\Factory\Extraction\DataExtractionContainerFactory;

final class HostedPaymentResultServiceTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::createHostedPaymentResult
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Result\HostedPaymentResult::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::__construct
     */
    public function testCreateHostedPaymentResultReturnsResult(): void
    {
        $service = $this->createService();

        $result = $service->createHostedPaymentResult('ORDER-123');

        self::assertSame('ORDER-123', $result->orderId);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Notification\HostedPaymentNotification::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::__construct
     */
    public function testParseHostedPaymentNotificationReturnsNotification(): void
    {
        $service = $this->createService();

        $notification = $service->parseHostedPaymentNotification($this->getPayload(), 'security-token');

        self::assertSame('ORDER-123', $notification->operation->orderId);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::parseHostedPaymentNotification
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService::__construct
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
        $dataExtractionContainer = (new DataExtractionContainerFactory())->createDataExtractionContainer(true);

        return new HostedPaymentResultService(
            $dataExtractionContainer,
            new PaymentOperationFactory($dataExtractionContainer),
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
