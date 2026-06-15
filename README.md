# italiamultimedia/xpay-web

PHP library for Nexi XPay Web / Phoenix API-key integrations.

This package targets the API-key based XPay Web endpoints, not the legacy Alias + MAC integration.

Implemented functionality:

* Hosted Payment Page creation: `POST /orders/hpp`
* Order status retrieval: `GET /orders/{orderId}`
* Hosted payment result handling
* Hosted payment notification parsing
* Nexi API error parsing
* Customer-facing Nexi error messages in English and Italian

## Installation

```shell
composer require italiamultimedia/xpay-web
```

The simple payment service needs:

* a PSR-18 HTTP client
* PSR-17 request and stream factories

For a plain PHP project, one simple combination is:

```shell
composer require php-http/curl-client nyholm/psr7
```

Frameworks may already provide these services; in that case, pass your framework's PSR implementations instead.

## Configuration

You need two values from your application configuration:

* your Nexi API key
* the Nexi environment: `test` or `production`

The environment matters because it decides which Nexi API base URL is used:

* `Configuration::ENVIRONMENT_TEST` uses the Nexi sandbox API
* `Configuration::ENVIRONMENT_PRODUCTION` uses the live Nexi API

Use the sandbox environment with a sandbox API key. Use the production environment only with a production API key.

Example `.env` values:

```dotenv
XPAY_API_KEY=your-api-key
XPAY_ENVIRONMENT=test
```

This package does not read `.env` files directly. Read those values with your framework/configuration layer, then pass them into `PaymentSystemSettings`.

Plain PHP example:

```php
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;

$apiKey = (string) getenv('XPAY_API_KEY');
$environment = (string) (getenv('XPAY_ENVIRONMENT') ?: Configuration::ENVIRONMENT_TEST);

$paymentSystemSettings = new PaymentSystemSettings($apiKey, $environment);
```

You can also pass the strings directly:

```php
$paymentSystemSettings = new PaymentSystemSettings($apiKey, 'test');
```

Using `Configuration::ENVIRONMENT_TEST` and `Configuration::ENVIRONMENT_PRODUCTION` is preferred because it avoids typos.

## Create Services

Create one `DependencyContainer` with your payment settings, then use `PaymentServiceFactory` to build services.

```php
use Http\Client\Curl\Client;
use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

$dependencyContainer = new DependencyContainer($paymentSystemSettings);
$paymentServiceFactory = new PaymentServiceFactory($dependencyContainer);

$psr17Factory = new Psr17Factory();

$simplePaymentService = $paymentServiceFactory->createSimplePaymentService(
    new Client(),
    $psr17Factory,
    $psr17Factory,
);

$hostedPaymentResultService = $paymentServiceFactory->createHostedPaymentResultService();
```

## Create A Hosted Payment Page

Create an order in your application first, then send Nexi the hosted payment page request.

```php
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;

$request = new CreateHostedPaymentPageRequest(
    $correlationId,
    $orderId,
    1000,
    Configuration::CURRENCY,
    'ITA',
    'https://example.com/payment/result',
    'https://example.com/payment/cancel',
    null,
    'Order description',
);

$response = $simplePaymentService->createHostedPaymentPage($request);

// Store this with your local order if you use hosted payment notifications.
$securityToken = $response->securityToken;

header(sprintf('Location: %s', $response->hostedPage));
exit;
```

Notes:

* `correlationId` must be a UUID v4.
* `orderId` should be your local payment/order identifier.
* Amounts are expressed in minor units, so `1000` means EUR 10.00 when using `Configuration::CURRENCY`.
* `resultUrl` is where Nexi redirects the customer after payment.
* `cancelUrl` is where Nexi redirects the customer after cancellation.
* `notificationUrl` is optional. Pass `null` unless you have a real public HTTPS webhook listener.

If you do have a webhook listener:

```php
$request = new CreateHostedPaymentPageRequest(
    $correlationId,
    $orderId,
    1000,
    Configuration::CURRENCY,
    'ITA',
    'https://example.com/payment/result',
    'https://example.com/payment/cancel',
    'https://example.com/payment/notification',
    'Order description',
);
```

## Verify Payment Status

After the customer returns to your `resultUrl`, do not trust only the redirect parameters. Verify the order through Nexi:

```php
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\RetrieveOrderStatusRequest;

$response = $simplePaymentService->retrieveOrderStatus(
    new RetrieveOrderStatusRequest($correlationId, $orderId),
);

foreach ($response->operations as $operation) {
    // Inspect operationResult, operationType, operationId, operationTime, etc.
}
```

The result redirect may include `paymentId`, but this library treats your local `orderId` as the source of truth.

## Hosted Result And Notifications

The result URL should already be tied to the local order.

```php
$result = $hostedPaymentResultService->createHostedPaymentResult($orderId);
```

For server-to-server notifications, Nexi posts JSON to your `notificationUrl`. Parse that received payload and validate it with the security token you stored when creating the hosted payment page:

```php
$notification = $hostedPaymentResultService->parseHostedPaymentNotification(
    $notificationData,
    $expectedSecurityToken,
);
```

For notification-only parsing, `new DependencyContainer()` is enough because payment settings are not needed.

## Error Handling

API errors are thrown as `NexiApiException`.

```php
use ItaliaMultimedia\XPayWeb\Service\Error\NexiErrorMessageService;
use ItaliaMultimedia\XPayWeb\Service\Exception\NexiApiException;

try {
    $response = $simplePaymentService->createHostedPaymentPage($request);
} catch (NexiApiException $exception) {
    $technicalMessage = $exception->getMessage();
    $customerMessage = (new NexiErrorMessageService())->getCustomerMessage($exception, 'it');
}
```

Supported customer message language codes are `en` and `it`. Unknown languages fall back to English.

## Recurring Payments

Recurring payments are not implemented yet.

Expected next work:

* Add request and response data transfers for recurring initial and subsequent payments.
* Add service methods and factories for the Nexi recurring-payment endpoints.
* Define how the merchant application stores recurring payment identifiers/tokens.
* Add sandbox scripts and unit tests for initial authorization and subsequent charges.

## Manual Sandbox Scripts

```shell
php bin/create-hosted-payment-page-test.php
php bin/retrieve-order-status-test.php <orderId> [correlationId]
```

The sandbox scripts do not test server-to-server notifications. To test notification parsing, point
`notificationUrl` at a real listener in your application and pass the received payload to
`HostedPaymentResultService::parseHostedPaymentNotification()`.

## Development

```shell
composer check:lint
composer check:phpcs
composer check:phpstan
composer check:phan
composer check:phpmd
composer check:psalm
composer check
composer test
```
