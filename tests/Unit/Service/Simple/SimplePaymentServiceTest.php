<?php

declare(strict_types=1);

namespace Tests\Unit\Service\Simple;

use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest;
use ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory;
use ItaliaMultimedia\XPayWeb\Service\Exception\NexiApiException;
use ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Tests\Unit\TestDouble\QueueHttpClient;

use function json_encode;

use const JSON_THROW_ON_ERROR;

final class SimplePaymentServiceTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateHostedPaymentPageResponse::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Simple\AbstractSimplePaymentService
     */
    public function testCreateHostedPaymentPageSendsJsonRequest(): void
    {
        $httpClient = new QueueHttpClient(
            new Response(200, [], '{"hostedPage":"https://gateway.example/hpp","securityToken":"security-token"}'),
        );
        $service = $this->createService($httpClient);
        $request = $this->createHostedPaymentPageRequest();

        $response = $service->createHostedPaymentPage($request);
        $sentRequest = $httpClient->getRequest();

        self::assertSame('https://gateway.example/hpp', $response->hostedPage);
        self::assertSame('security-token', $response->securityToken);
        self::assertSame('POST', $sentRequest->getMethod());
        self::assertSame(
            'https://xpaysandbox.nexigroup.com/api/phoenix-0.0/psp/api/v1/orders/hpp',
            (string) $sentRequest->getUri(),
        );
        self::assertSame(['application/json'], $sentRequest->getHeader('Content-Type'));
        self::assertSame(['application/json'], $sentRequest->getHeader('Accept'));
        self::assertSame(['api-key'], $sentRequest->getHeader('X-API-KEY'));
        self::assertSame([$request->correlationId], $sentRequest->getHeader('Correlation-Id'));
        self::assertJsonStringEqualsJsonString(
            json_encode($request->toArray(), JSON_THROW_ON_ERROR),
            (string) $sentRequest->getBody(),
        );
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Simple\AbstractSimplePaymentService
     */
    public function testRetrieveOrderStatusSendsGetRequestAndParsesResponse(): void
    {
        $httpClient = new QueueHttpClient(
            new Response(200, [], json_encode($this->getOrderPayload(), JSON_THROW_ON_ERROR)),
        );
        $service = $this->createService($httpClient);

        $response = $service->retrieveOrderStatus(
            new RetrieveOrderStatusRequest('2f0ea505-9b41-414a-b374-4fe672327d85', 'ORDER-123'),
        );
        $sentRequest = $httpClient->getRequest();

        self::assertSame('GET', $sentRequest->getMethod());
        self::assertSame(
            'https://xpaysandbox.nexigroup.com/api/phoenix-0.0/psp/api/v1/orders/ORDER-123',
            (string) $sentRequest->getUri(),
        );
        self::assertSame(['api-key'], $sentRequest->getHeader('X-API-KEY'));
        self::assertSame(['2f0ea505-9b41-414a-b374-4fe672327d85'], $sentRequest->getHeader('Correlation-Id'));
        self::assertSame('ORDER-123', $response->orderId);
        $operations = $response->operations;
        $operation = $operations[0] ?? null;
        if (!$operation instanceof PaymentOperation) {
            self::fail('Expected payment operation.');
        }

        self::assertSame('AUTHORIZED', $operation->operationResult);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Simple\AbstractSimplePaymentService
     */
    public function testRetrieveOrderStatusDoesNotRequireOperationTime(): void
    {
        $payload = $this->getOrderPayload();
        $orderStatus = $payload['orderStatus'] ?? null;
        self::assertIsArray($orderStatus);
        $operations = $orderStatus['operations'] ?? null;
        self::assertIsArray($operations);
        $operation = $operations[0] ?? null;
        self::assertIsArray($operation);
        unset($operation['operationTime']);
        $operations[0] = $operation;
        $orderStatus['operations'] = $operations;
        $payload['orderStatus'] = $orderStatus;
        $httpClient = new QueueHttpClient(
            new Response(200, [], json_encode($payload, JSON_THROW_ON_ERROR)),
        );
        $service = $this->createService($httpClient);

        $response = $service->retrieveOrderStatus(
            new RetrieveOrderStatusRequest('2f0ea505-9b41-414a-b374-4fe672327d85', 'ORDER-123'),
        );
        $operation = $response->operations[0] ?? null;
        if (!$operation instanceof PaymentOperation) {
            self::fail('Expected payment operation.');
        }

        self::assertNull($operation->operationTime);
        self::assertSame('AUTHORIZED', $operation->operationResult);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\NexiError::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Exception\NexiApiException
     * @uses \ItaliaMultimedia\XPayWeb\Service\Simple\AbstractSimplePaymentService
     */
    public function testCreateHostedPaymentPageThrowsNexiApiExceptionWithErrorBody(): void
    {
        $errorBody = '{"errors":[{"code":"GW0001","description":"Invalid merchant URL"}]}';
        $httpClient = new QueueHttpClient(new Response(400, [], $errorBody));
        $service = $this->createService($httpClient);

        try {
            $service->createHostedPaymentPage($this->createHostedPaymentPageRequest());

            self::fail('Expected Nexi API exception.');
        } catch (NexiApiException $nexiApiException) {
            self::assertSame(400, $nexiApiException->getStatusCode());
            self::assertSame($errorBody, $nexiApiException->getResponseBody());
            $errors = $nexiApiException->getErrors();
            self::assertArrayHasKey(0, $errors);
            if (!isset($errors[0])) {
                self::fail('Expected Nexi error.');
            }

            self::assertSame('GW0001', $errors[0]->code);
            self::assertStringContainsString('GW0001 Invalid merchant URL', $nexiApiException->getMessage());
        }
    }

    private function createHostedPaymentPageRequest(): CreateHostedPaymentPageRequest
    {
        return new CreateHostedPaymentPageRequest(
            '2f0ea505-9b41-414a-b374-4fe672327d85',
            'ORDER-123',
            1000,
            'EUR',
            'ENG',
            'https://example.com/payment/result',
            'https://example.com/payment/cancel',
        );
    }

    private function createService(QueueHttpClient $httpClient): SimplePaymentService
    {
        $psr17Factory = new Psr17Factory();
        $dependencyContainer = new DependencyContainer(
            new PaymentSystemSettings('api-key', Configuration::ENVIRONMENT_TEST),
        );

        return (new PaymentServiceFactory($dependencyContainer))->createSimplePaymentService(
            $httpClient,
            $psr17Factory,
            $psr17Factory,
        );
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @return array<mixed>
     */
    private function getOrderPayload(): array
    {
        return [
            'orderStatus' => [
                'authorizedAmount' => '1000',
                'operations' => [
                    [
                        'operationAmount' => '1000',
                        'operationCurrency' => 'EUR',
                        'operationId' => '3470744',
                        'operationResult' => 'AUTHORIZED',
                        'operationTime' => '2022-09-01T01:20:00.001Z',
                        'operationType' => 'AUTHORIZATION',
                        'orderId' => 'ORDER-123',
                    ],
                ],
                'order' => [
                    'amount' => '1000',
                    'currency' => 'EUR',
                    'orderId' => 'ORDER-123',
                ],
            ],
        ];
    }
}
