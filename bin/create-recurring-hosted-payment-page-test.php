<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Http\Client\Curl\Client;
use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageOptions;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence;
use ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

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

$orderId = sprintf('TEST%d', time());
$contractId = sprintf('CONTRACT%d', time());

$request = new CreateHostedPaymentPageRequest(
    '2f0ea505-9b41-414a-b374-4fe672327d85',
    $orderId,
    100,
    Configuration::CURRENCY,
    'ENG',
    'https://example.com/payment/result',
    'https://example.com/payment/cancel',
    new CreateHostedPaymentPageOptions(
        description: 'XPay Web Sandbox Recurring Test',
        recurrence: new HostedPaymentPageRecurrence(
            $contractId,
            HostedPaymentPageRecurrence::CONTRACT_TYPE_MIT_UNSCHEDULED,
        ),
    ),
);

try {
    $response = $service->createHostedPaymentPage($request);

    echo 'Order ID: ' . $orderId . PHP_EOL;
    echo 'Contract ID: ' . $contractId . PHP_EOL;
    echo 'Hosted page: ' . $response->hostedPage . PHP_EOL;
    echo 'Security token: ' . $response->securityToken . PHP_EOL;
    echo 'Status command: php bin/retrieve-order-status-test.php ' . $orderId . PHP_EOL;
} catch (Throwable $throwable) {
    $rawResponse = $service->getResponse();

    echo $throwable->getMessage() . PHP_EOL;

    if ($rawResponse !== null) {
        echo sprintf('Status: %d%s', $rawResponse->getStatusCode(), PHP_EOL);
        echo sprintf('Body: %s%s', (string) $rawResponse->getBody(), PHP_EOL);
    }

    throw $throwable;
}
