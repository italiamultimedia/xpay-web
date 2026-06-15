<?php

declare(strict_types=1);

namespace Tests\Unit\Factory\Service;

use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Tests\Unit\TestDouble\QueueHttpClient;

final class PaymentServiceFactoryTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory::createHostedPaymentResultService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\Result\HostedPaymentResult
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService
     */
    public function testCreateHostedPaymentResultService(): void
    {
        $paymentServiceFactory = new PaymentServiceFactory(new DependencyContainer());

        $result = $paymentServiceFactory->createHostedPaymentResultService()
            ->createHostedPaymentResult('ORDER-123');

        self::assertSame('ORDER-123', $result->orderId);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory::createSimplePaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Simple\AbstractSimplePaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService
     */
    public function testCreateSimplePaymentService(): void
    {
        $psr17Factory = new Psr17Factory();
        $dependencyContainer = new DependencyContainer(
            new PaymentSystemSettings('api-key', Configuration::ENVIRONMENT_TEST),
        );

        $paymentServiceFactory = new PaymentServiceFactory($dependencyContainer);

        $service = $paymentServiceFactory->createSimplePaymentService(
            new QueueHttpClient(),
            $psr17Factory,
            $psr17Factory,
        );

        self::assertSame(
            'https://xpaysandbox.nexigroup.com/api/phoenix-0.0/psp/api/v1/orders/hpp',
            $service->getHostedPaymentPageApiUrl(),
        );
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory::createRecurringPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory
     * @uses \ItaliaMultimedia\XPayWeb\Factory\Service\PaymentServiceFactory::__construct
     * @uses \ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Recurring\AbstractRecurringPaymentService
     * @uses \ItaliaMultimedia\XPayWeb\Service\Recurring\RecurringPaymentService
     */
    public function testCreateRecurringPaymentService(): void
    {
        $psr17Factory = new Psr17Factory();
        $dependencyContainer = new DependencyContainer(
            new PaymentSystemSettings('api-key', Configuration::ENVIRONMENT_TEST),
        );

        $paymentServiceFactory = new PaymentServiceFactory($dependencyContainer);

        $service = $paymentServiceFactory->createRecurringPaymentService(
            new QueueHttpClient(),
            $psr17Factory,
            $psr17Factory,
        );

        self::assertSame(
            'https://xpaysandbox.nexigroup.com/api/phoenix-0.0/psp/api/v1/orders/mit',
            $service->getRecurringPaymentApiUrl(),
        );
    }
}
