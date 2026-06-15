<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use ItaliaMultimedia\XPayWeb\Container\DependencyContainer;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use LogicException;
use PHPUnit\Framework\TestCase;

final class DependencyContainerTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\Container\DependencyContainer::getPaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer::__construct
     * @uses \ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings
     */
    public function testGetPaymentSystemSettingsReturnsConfiguredSettings(): void
    {
        $paymentSystemSettings = new PaymentSystemSettings('api-key', Configuration::ENVIRONMENT_TEST);
        $dependencyContainer = new DependencyContainer($paymentSystemSettings);

        self::assertSame($paymentSystemSettings, $dependencyContainer->getPaymentSystemSettings());
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Container\DependencyContainer::getPaymentSystemSettings
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer::__construct
     */
    public function testGetPaymentSystemSettingsRequiresConfiguredSettings(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Payment system settings are not configured.');

        (new DependencyContainer())->getPaymentSystemSettings();
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\Container\DependencyContainer::getDataExtractionContainer
     * @uses \ItaliaMultimedia\XPayWeb\Container\DependencyContainer::__construct
     */
    public function testGetDataExtractionContainerReusesCreatedContainer(): void
    {
        $dependencyContainer = new DependencyContainer();

        self::assertSame(
            $dependencyContainer->getDataExtractionContainer(),
            $dependencyContainer->getDataExtractionContainer(),
        );
    }
}
