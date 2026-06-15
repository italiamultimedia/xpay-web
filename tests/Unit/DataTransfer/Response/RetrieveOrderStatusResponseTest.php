<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransfer\Response;

use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse;
use PHPUnit\Framework\TestCase;

final class RetrieveOrderStatusResponseTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation::__construct
     */
    public function testConstructorStoresOrderStatusData(): void
    {
        $response = $this->createResponse();

        self::assertSame('ORDER-123', $response->orderId);
        self::assertSame('1000', $response->orderAmount);
        self::assertSame('EUR', $response->orderCurrency);
        self::assertSame('1000', $response->authorizedAmount);
        self::assertSame('CAPTURE', $response->lastOperationType);
        $operations = $response->operations;
        $operation = $operations[0] ?? null;
        if (!$operation instanceof PaymentOperation) {
            self::fail('Expected payment operation.');
        }

        self::assertSame('AUTHORIZED', $operation->operationResult);
        $additionalData = $operation->additionalData;
        self::assertSame('647189', $additionalData['authorizationCode'] ?? null);
    }

    private function createOperation(): PaymentOperation
    {
        return new PaymentOperation(
            'ORDER-123',
            '3470744',
            'AUTHORIZATION',
            'AUTHORIZED',
            '2022-09-01T01:20:00.001Z',
            '1000',
            'EUR',
            [
                'authorizationCode' => '647189',
            ],
            $this->getOperationPayload(),
        );
    }

    private function createResponse(): RetrieveOrderStatusResponse
    {
        return new RetrieveOrderStatusResponse(
            'ORDER-123',
            '1000',
            'EUR',
            '1000',
            '1000',
            'CAPTURE',
            '2022-09-01T01:20:00.001Z',
            [$this->createOperation()],
            $this->getOrderStatusPayload(),
        );
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     */
    private function getOrderStatusPayload(): array
    {
        return [
            'authorizedAmount' => '1000',
            'capturedAmount' => '1000',
            'lastOperationTime' => '2022-09-01T01:20:00.001Z',
            'lastOperationType' => 'CAPTURE',
            'operations' => [$this->getOperationPayload()],
            'order' => $this->getOrderPayload(),
        ];
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     */
    private function getOperationPayload(): array
    {
        return [
            'additionalData' => [
                'authorizationCode' => '647189',
            ],
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

    /**
     * @return array<string,string>
     */
    private function getOrderPayload(): array
    {
        return [
            'amount' => '1000',
            'currency' => 'EUR',
            'description' => 'Test order',
            'orderId' => 'ORDER-123',
        ];
    }
}
