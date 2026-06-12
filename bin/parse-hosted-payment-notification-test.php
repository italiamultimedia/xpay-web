<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService;
use WebServCo\Data\Factory\Extraction\DataExtractionContainerFactory;

$securityToken = $argv[1] ?? null;
if (!is_string($securityToken) || $securityToken === '') {
    echo 'Usage: php bin/parse-hosted-payment-notification-test.php <securityToken> [notificationJsonFile]' . PHP_EOL;
    echo 'Pass the JSON file as the second argument, or pipe the notification JSON through STDIN.' . PHP_EOL;

    exit(1);
}

$json = isset($argv[2])
    ? file_get_contents($argv[2])
    : file_get_contents('php://stdin');

if (!is_string($json) || $json === '') {
    echo 'Notification JSON is empty.' . PHP_EOL;

    exit(1);
}

$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($data)) {
    echo 'Notification JSON must decode to an object.' . PHP_EOL;

    exit(1);
}

$dataExtractionContainer = (new DataExtractionContainerFactory())->createDataExtractionContainer(true);
$service = new HostedPaymentResultService($dataExtractionContainer);

try {
    $notification = $service->parseHostedPaymentNotification($data, $securityToken);
    $operation = $notification->operation;

    echo 'Notification is valid.' . PHP_EOL;
    echo 'Event ID: ' . $notification->eventId . PHP_EOL;
    echo 'Event time: ' . $notification->eventTime . PHP_EOL;
    echo 'Order ID: ' . $operation->orderId . PHP_EOL;
    echo 'Operation ID: ' . $operation->operationId . PHP_EOL;
    echo 'Operation type: ' . $operation->operationType . PHP_EOL;
    echo 'Operation result: ' . $operation->operationResult . PHP_EOL;
    echo sprintf('Operation time: %s%s', $operation->operationTime, PHP_EOL);
} catch (Throwable $throwable) {
    echo $throwable->getMessage() . PHP_EOL;

    throw $throwable;
}
