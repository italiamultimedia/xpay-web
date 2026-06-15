<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Http\Client\Curl\Client;
use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest;
use ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

$orderId = $argv[1] ?? null;
if (!is_string($orderId) || $orderId === '') {
    echo 'Usage: php bin/retrieve-order-status-test.php <orderId> [correlationId]' . PHP_EOL;

    exit(1);
}

$correlationId = $argv[2] ?? '2f0ea505-9b41-414a-b374-4fe672327d85';

$dependencyContainer = new DependencyContainer(
    new PaymentSystemSettings(
        '2e570a58-9914-477a-9ede-35baff23a376',
        Configuration::ENVIRONMENT_TEST,
    ),
);

$psr17Factory = new Psr17Factory();
$paymentServiceFactory = new PaymentServiceFactory($dependencyContainer);
$service = $paymentServiceFactory->createSimplePaymentService(
    new Client(),
    $psr17Factory,
    $psr17Factory,
);

try {
    $response = $service->retrieveOrderStatus(new RetrieveOrderStatusRequest($correlationId, $orderId));
    $operations = $response->operations;

    echo 'Order ID: ' . $response->orderId . PHP_EOL;
    echo 'Order amount: ' . $response->orderAmount . PHP_EOL;
    echo 'Order currency: ' . $response->orderCurrency . PHP_EOL;
    echo 'Authorized amount: ' . ($response->authorizedAmount ?? '') . PHP_EOL;
    echo 'Captured amount: ' . ($response->capturedAmount ?? '') . PHP_EOL;
    echo 'Last operation type: ' . ($response->lastOperationType ?? '') . PHP_EOL;
    echo 'Last operation time: ' . ($response->lastOperationTime ?? '') . PHP_EOL;
    echo sprintf('Operations: %d%s', count($operations), PHP_EOL);

    foreach ($operations as $operation) {
        if (!$operation instanceof PaymentOperation) {
            continue;
        }

        echo sprintf(
            '- %s %s %s at %s%s',
            $operation->operationType,
            $operation->operationResult,
            $operation->operationId,
            $operation->operationTime,
            PHP_EOL,
        );
    }

    echo 'Raw orderStatus:' . PHP_EOL;
    echo json_encode($response->rawData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
} catch (Throwable $throwable) {
    $rawResponse = $service->getResponse();

    echo $throwable->getMessage() . PHP_EOL;

    if ($rawResponse !== null) {
        echo sprintf('Status: %d%s', $rawResponse->getStatusCode(), PHP_EOL);
        echo sprintf('Body: %s%s', (string) $rawResponse->getBody(), PHP_EOL);
    }

    throw $throwable;
}
