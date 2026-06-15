# italiamultimedia/xpay-web

PHP library for Nexi XPay Web / Phoenix API-key integrations.

This package targets the API-key based XPay Web endpoints, not the legacy Alias + MAC integration.

Implemented functionality:

* Hosted Payment Page creation: `POST /orders/hpp`
* Recurring contract creation through Hosted Payment Page recurrence data
* Subsequent recurring payments: `POST /orders/mit`
* Order status retrieval: `GET /orders/{orderId}`
* Hosted payment result handling
* Hosted payment notification parsing
* Nexi API error parsing
* Customer-facing Nexi error messages in English and Italian

## Installation

```shell
composer require italiamultimedia/xpay-web
```

The HTTP payment services need:

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

$recurringPaymentService = $paymentServiceFactory->createRecurringPaymentService(
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
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageOptions;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;

$request = new CreateHostedPaymentPageRequest(
    $correlationId,
    $orderId,
    1000,
    Configuration::CURRENCY,
    'ITA',
    'https://example.com/payment/result',
    'https://example.com/payment/cancel',
    new CreateHostedPaymentPageOptions(description: 'Order description'),
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
* `recurrence` is optional. Pass it only when this hosted payment should create a recurring payment contract.

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
    new CreateHostedPaymentPageOptions(
        notificationUrl: 'https://example.com/payment/notification',
        description: 'Order description',
    ),
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

## Recurring Hosted Payments

Nexi recurring payments are MIT payments: Merchant Initiated Transactions. The first customer-present
payment creates a contract, then later charges can be made against that contract.

This package supports both phases:

* first payment through Hosted Payment Page with a `recurrence` object inside `CreateHostedPaymentPageRequest`
* later merchant-initiated charges through `POST /orders/mit`

For the first payment, Nexi still receives the same `POST /orders/hpp` request; the recurrence data tells
Nexi to create a contract from the card used on the hosted page.

Use `MIT_SCHEDULED` when the later merchant charges have a defined schedule, for example every 30 days
or on the first day of each month. Nexi also accepts `contractExpiryDate` and `contractFrequency` for
scheduled contracts.

Use `MIT_UNSCHEDULED` when the later merchant charges do not have a fixed schedule, for example usage-based
or variable-date billing.

Scheduled example:

```php
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageOptions;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateHostedPaymentPageRequest;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence;

$contractId = 'CUSTOMER123PLAN1';

$request = new CreateHostedPaymentPageRequest(
    $correlationId,
    $orderId,
    1000,
    Configuration::CURRENCY,
    'ITA',
    'https://example.com/payment/result',
    'https://example.com/payment/cancel',
    new CreateHostedPaymentPageOptions(
        notificationUrl: 'https://example.com/payment/notification',
        description: 'Subscription first payment',
        recurrence: HostedPaymentPageRecurrence::createMitScheduled($contractId, '2027-12-31', '30'),
    ),
);
```

Unscheduled example:

```php
$request = new CreateHostedPaymentPageRequest(
    $correlationId,
    $orderId,
    1000,
    Configuration::CURRENCY,
    'ITA',
    'https://example.com/payment/result',
    'https://example.com/payment/cancel',
    new CreateHostedPaymentPageOptions(
        description: 'Usage based first payment',
        recurrence: HostedPaymentPageRecurrence::createMitUnscheduled($contractId),
    ),
);
```

Store the `contractId` in your application together with the customer/subscription. Nexi expects it to be
unique in your merchant domain.

Subsequent recurring charges are the second phase of Nexi recurring payments and use `POST /orders/mit`.
They are server-to-server calls and do not redirect the customer back to the hosted page.

```php
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentOptions;
use ItaliaMultimedia\XPayWeb\DataTransfer\Request\CreateSubsequentRecurringPaymentRequest;

$response = $recurringPaymentService->createSubsequentRecurringPayment(
    new CreateSubsequentRecurringPaymentRequest(
        $correlationId,
        $idempotencyKey,
        $orderId,
        1000,
        Configuration::CURRENCY,
        $contractId,
        new CreateSubsequentRecurringPaymentOptions(
            CreateSubsequentRecurringPaymentOptions::CAPTURE_TYPE_IMPLICIT,
            $customerId,
            'Subscription renewal',
        ),
    ),
);

$operation = $response->operation;
```

For subsequent payments:

* `correlationId` must be a UUID v4.
* `idempotencyKey` must be a UUID v4 and should be unique for that payment attempt. Reuse the same key
  only when retrying the same charge after a transport failure.
* `orderId` is the new merchant order/payment identifier for the recurring charge.
* `contractId` is the contract created during the first hosted payment.
* `captureType` is optional. Use `CAPTURE_TYPE_IMPLICIT` for automatic confirmation or
  `CAPTURE_TYPE_EXPLICIT` for authorization only, if your terminal configuration allows it.

## Manual Sandbox Scripts

```shell
php bin/create-hosted-payment-page-test.php
php bin/create-recurring-hosted-payment-page-test.php
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
