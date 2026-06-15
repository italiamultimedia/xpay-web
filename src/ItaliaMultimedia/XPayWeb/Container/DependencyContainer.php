<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Container;

use ItaliaMultimedia\XPayWeb\DataTransfer\PaymentSystemSettings;
use ItaliaMultimedia\XPayWeb\Factory\NexiApiExceptionFactory;
use ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory;
use LogicException;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerFactoryInterface;
use WebServCo\Data\Contract\Extraction\DataExtractionContainerInterface;
use WebServCo\Data\Factory\Extraction\DataExtractionContainerFactory;

final class DependencyContainer
{
    private DataExtractionContainerFactoryInterface $dataExtractionContainerFactory;

    private ?DataExtractionContainerInterface $dataExtractionContainer = null;
    private ?NexiApiExceptionFactory $nexiApiExceptionFactory = null;
    private ?PaymentOperationFactory $paymentOperationFactory = null;

    public function __construct(private ?PaymentSystemSettings $paymentSystemSettings = null)
    {
        $this->dataExtractionContainerFactory = new DataExtractionContainerFactory();
    }

    public function getDataExtractionContainer(): DataExtractionContainerInterface
    {
        if ($this->dataExtractionContainer === null) {
            $this->dataExtractionContainer = $this->dataExtractionContainerFactory->createDataExtractionContainer(true);
        }

        return $this->dataExtractionContainer;
    }

    public function getNexiApiExceptionFactory(): NexiApiExceptionFactory
    {
        if ($this->nexiApiExceptionFactory === null) {
            $this->nexiApiExceptionFactory = new NexiApiExceptionFactory($this->getDataExtractionContainer());
        }

        return $this->nexiApiExceptionFactory;
    }

    public function getPaymentSystemSettings(): PaymentSystemSettings
    {
        if ($this->paymentSystemSettings === null) {
            throw new LogicException('Payment system settings are not configured.');
        }

        return $this->paymentSystemSettings;
    }

    public function getPaymentOperationFactory(): PaymentOperationFactory
    {
        if ($this->paymentOperationFactory === null) {
            $this->paymentOperationFactory = new PaymentOperationFactory($this->getDataExtractionContainer());
        }

        return $this->paymentOperationFactory;
    }
}
