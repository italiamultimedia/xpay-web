<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Recurring;

use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentOptions;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest;
use ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory;
use ItaliaMultimedia\XPayWeb\Service\Recurring\RecurringPaymentService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tests\Unit\TestDouble\QueueHttpClient;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final class RecurringPaymentServiceTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\Recurring\RecurringPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentOptions
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateSubsequentRecurringPaymentResponse
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Recurring\AbstractRecurringPaymentService
     */
    public function testCreateSubsequentRecurringPaymentSendsJsonRequestAndParsesResponse(): void
    {
        $httpClient = new QueueHttpClient(
            new Response(200, [], json_encode($this->getOperationPayload(), JSON_THROW_ON_ERROR)),
        );
        $service = $this->createService($httpClient);
        $request = $this->createSubsequentRecurringPaymentRequest();

        $response = $service->createSubsequentRecurringPayment($request);
        $sentRequest = $httpClient->getRequest();

        $this->assertSentRequest($sentRequest, $request);
        self::assertSame('ORDER-124', $response->operation->orderId);
        self::assertSame(
            'MERCHANT_INITIATED_TRANSACTION',
            $response->operation->additionalData['channelDetail'] ?? null,
        );
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\Recurring\RecurringPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentOptions
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateSubsequentRecurringPaymentResponse
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Recurring\AbstractRecurringPaymentService
     */
    public function testCreateSubsequentRecurringPaymentDoesNotRequireOperationTime(): void
    {
        $payload = $this->getOperationPayload();
        $operation = $payload['operation'] ?? null;
        self::assertIsArray($operation);
        unset($operation['operationTime']);
        $payload['operation'] = $operation;
        $httpClient = new QueueHttpClient(
            new Response(200, [], json_encode($payload, JSON_THROW_ON_ERROR)),
        );
        $service = $this->createService($httpClient);

        $response = $service->createSubsequentRecurringPayment($this->createSubsequentRecurringPaymentRequest());

        self::assertNull($response->operation->operationTime);
        self::assertSame('ORDER-124', $response->operation->orderId);
    }

    private function assertSentRequest(
        RequestInterface $sentRequest,
        CreateSubsequentRecurringPaymentRequest $request,
    ): void {
        self::assertSame('POST', $sentRequest->getMethod());
        self::assertSame(
            'https://xpaysandbox.nexigroup.com/api/phoenix-0.0/psp/api/v1/orders/mit',
            (string) $sentRequest->getUri(),
        );
        self::assertSame(['application/json'], $sentRequest->getHeader('Content-Type'));
        self::assertSame(['application/json'], $sentRequest->getHeader('Accept'));
        self::assertSame(['api-key'], $sentRequest->getHeader('X-API-KEY'));
        self::assertSame([$request->correlationId], $sentRequest->getHeader('Correlation-Id'));
        self::assertSame([$request->idempotencyKey], $sentRequest->getHeader('Idempotency-Key'));
        self::assertJsonStringEqualsJsonString(
            json_encode($request->toArray(), JSON_THROW_ON_ERROR),
            (string) $sentRequest->getBody(),
        );
    }

    private function createService(QueueHttpClient $httpClient): RecurringPaymentService
    {
        $psr17Factory = new Psr17Factory();
        $dependencyContainer = new DependencyContainer(
            new PaymentSystemSettings('api-key', Configuration::ENVIRONMENT_TEST),
        );

        return (new PaymentServiceFactory($dependencyContainer))->createRecurringPaymentService(
            $httpClient,
            $psr17Factory,
            $psr17Factory,
        );
    }

    private function createSubsequentRecurringPaymentRequest(): CreateSubsequentRecurringPaymentRequest
    {
        return new CreateSubsequentRecurringPaymentRequest(
            '2f0ea505-9b41-414a-b374-4fe672327d85',
            'f4c1a3fd-63f7-4f19-b229-dcc8d0ca5923',
            'ORDER-124',
            1000,
            Configuration::CURRENCY,
            'CONTRACT-123',
            new CreateSubsequentRecurringPaymentOptions(
                CreateSubsequentRecurringPaymentOptions::CAPTURE_TYPE_IMPLICIT,
                'CUSTOMER-123',
                'Subscription renewal',
                'Plan A',
            ),
        );
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     */
    private function getOperationPayload(): array
    {
        return [
            'operation' => [
                'additionalData' => [
                    'channelDetail' => 'MERCHANT_INITIATED_TRANSACTION',
                ],
                'operationAmount' => '1000',
                'operationCurrency' => 'EUR',
                'operationId' => '3470745',
                'operationResult' => 'AUTHORIZED',
                'operationTime' => '2022-09-01T01:20:00.001Z',
                'operationType' => 'AUTHORIZATION',
                'orderId' => 'ORDER-124',
            ],
        ];
    }
}
