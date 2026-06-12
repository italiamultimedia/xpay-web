# italiamultimedia/xpay-web

An XPay Web / Nexi implementation for API Key based integrations.

This package is intended for contracts using XPay Web / Phoenix APIs, not the legacy Alias + MAC XPay integration.

Currently implemented functionality:

* Hosted Payment Page creation: `POST /orders/hpp`
* Order status retrieval: `GET /orders/{orderId}`
* Hosted payment result handling
* Hosted payment notification parsing
* Nexi API error parsing
* Localized customer error messages: English and Italian

---

## Hosted Payment Page

### Create payment page

Use `SimplePaymentService` to create a Hosted Payment Page.

```php
$paymentSystemSettings = new PaymentSystemSettings(
    $apiKey,
    Configuration::ENVIRONMENT_TEST,
);

$service = new SimplePaymentService(
    $httpClient,
    $requestFactory,
    $streamFactory,
    $paymentSystemSettings,
    $dataExtractionContainer,
);

$request = new CreateHostedPaymentPageRequest(
    $correlationId,
    $orderId,
    1000,
    Configuration::CURRENCY,
    'ITA',
    'https://example.com/payment/result?lang=it',
    'https://example.com/payment/cancel?lang=it',
    'https://example.com/payment/notification?lang=it',
    'Order description',
);

$response = $service->createHostedPaymentPage($request);

header(sprintf('Location: %s', $response->hostedPage));
exit;
```

`correlationId` must be a UUID v4.

`orderId` must be a Nexi-safe order identifier. Avoid UUIDs with hyphens for `orderId`.

Amount is expressed in minor units:

```text
1000 = €10.00
```

---

## Retrieve order status

After the customer returns from Nexi, verify the payment using `GET /orders/{orderId}`.

```php
$response = $service->retrieveOrderStatus(
    new RetrieveOrderStatusRequest($correlationId, $orderId),
);

foreach ($response->operations as $operation) {
    // Inspect operationResult, operationType, operationId, etc.
}
```

The result redirect may contain `paymentId` / `paymentid`, but payment verification should be done through order status retrieval.

---

## Hosted payment result

The result URL should be created by the merchant and tied to the local order.

```php
$result = $hostedPaymentResultService->createHostedPaymentResult($orderId, $paymentId);
```

The `paymentId` is optional redirect metadata. The local `orderId` remains the source of truth for status retrieval.

---

## Notifications

Use `HostedPaymentResultService` to parse hosted payment notifications.

```php
$notification = $hostedPaymentResultService->parseHostedPaymentNotification(
    $notificationData,
    $expectedSecurityToken,
);
```

The notification security token is validated with `hash_equals`.

---

## Error handling

API errors are thrown as `NexiApiException`.

```php
try {
    $response = $service->createHostedPaymentPage($request);
} catch (NexiApiException $exception) {
    $technicalMessage = $exception->getMessage();
    $errors = $exception->getErrors();
}
```

Each parsed Nexi error contains:

```php
$error->code;
$error->description;
```

For customer-facing messages, use `NexiErrorMessageService`.

```php
$message = $nexiErrorMessageService->getCustomerMessage($exception, 'it');
```

Supported customer message languages:

* `en`
* `it`

Unknown languages fall back to English.

---

## Manual sandbox scripts

Create Hosted Payment Page:

```shell
php bin/create-hosted-payment-page-test.php
```

Retrieve order status:

```shell
php bin/retrieve-order-status-test.php <orderId>
```

Parse hosted payment notification:

```shell
php bin/parse-hosted-payment-notification-test.php <securityToken> [notificationJsonFile]
```

These scripts are for manual sandbox testing only.

---

## Development

```shell
composer check:lint
composer check:phpcs
composer check:phpstan
composer check:phan
composer check:phpmd
composer check:psalm
```

Or run all checks:

```shell
composer check
```

Run tests:

```shell
composer test
```
