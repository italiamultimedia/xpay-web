<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer;

final class Configuration
{
    public const string API_URL_PRODUCTION = 'https://xpay.nexigroup.com/api/phoenix-0.0/psp/api/v1/';

    public const string API_URL_TEST = 'https://xpaysandbox.nexigroup.com/api/phoenix-0.0/psp/api/v1/';

    public const string CURRENCY = 'EUR';

    public const string ENVIRONMENT_PRODUCTION = 'production';

    public const string ENVIRONMENT_TEST = 'test';

    public const string HOSTED_PAYMENT_PAGE_API_ENDPOINT = 'orders/hpp';

    public const string ORDER_API_ENDPOINT = 'orders/%s';
}
