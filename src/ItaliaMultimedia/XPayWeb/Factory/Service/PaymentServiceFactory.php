<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Factory\Service;

use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\Container\HttpDependencyContainer;
use ItaliaMultimedia\XPayWeb\Service\HostedPaymentResultService;
use ItaliaMultimedia\XPayWeb\Service\Recurring\RecurringPaymentService;
use ItaliaMultimedia\XPayWeb\Service\Simple\SimplePaymentService;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class PaymentServiceFactory
{
    public function __construct(private DependencyContainer $dependencyContainer)
    {
    }

    public function createHostedPaymentResultService(): HostedPaymentResultService
    {
        return new HostedPaymentResultService(
            $this->dependencyContainer->getDataExtractionContainer(),
            $this->dependencyContainer->getPaymentOperationFactory(),
        );
    }

    public function createSimplePaymentService(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): SimplePaymentService {
        return new SimplePaymentService(
            new HttpDependencyContainer($httpClient, $requestFactory, $streamFactory),
            $this->dependencyContainer->getPaymentSystemSettings(),
            $this->dependencyContainer->getNexiApiExceptionFactory(),
            $this->dependencyContainer->getDataExtractionContainer(),
            $this->dependencyContainer->getPaymentOperationFactory(),
        );
    }

    public function createRecurringPaymentService(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): RecurringPaymentService {
        return new RecurringPaymentService(
            new HttpDependencyContainer($httpClient, $requestFactory, $streamFactory),
            $this->dependencyContainer->getPaymentSystemSettings(),
            $this->dependencyContainer->getNexiApiExceptionFactory(),
            $this->dependencyContainer->getDataExtractionContainer(),
            $this->dependencyContainer->getPaymentOperationFactory(),
        );
    }
}
