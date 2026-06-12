<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Http\Client\Curl\Client;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService;
use Nyholm\Psr7\Factory\Psr17Factory;

$paymentSystemSettings = new PaymentSystemSettings(
    '2e570a58-9914-477a-9ede-35baff23a376',
    Configuration::ENVIRONMENT_TEST,
);

$psr17Factory = new Psr17Factory();

$service = new SimplePaymentService(
    new Client(),
    $psr17Factory,
    $psr17Factory,
    $paymentSystemSettings,
);

$request = new CreateHostedPaymentPageRequest(
    '2f0ea505-9b41-414a-b374-4fe672327d85',
    sprintf('TEST%d', time()),
    100,
    Configuration::CURRENCY,
    'ENG',
    'https://example.com/payment/result',
    'https://example.com/payment/cancel',
    'https://example.com/payment/notification',
    'XPay Web Sandbox Test',
);

try {
    $response = $service->createHostedPaymentPage($request);

    echo 'Hosted page: ' . $response->hostedPage . PHP_EOL;
    echo 'Security token: ' . $response->securityToken . PHP_EOL;
} catch (Throwable $throwable) {
    $rawResponse = $service->getResponse();

    echo $throwable->getMessage() . PHP_EOL;

    if ($rawResponse !== null) {
        echo sprintf('Status: %d%s', $rawResponse->getStatusCode(), PHP_EOL);
        echo sprintf('Body: %s%s', (string) $rawResponse->getBody(), PHP_EOL);
    }

    throw $throwable;
}
