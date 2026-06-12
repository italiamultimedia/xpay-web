<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\Service\Simple;

use ItaliaMultimedia\XPayWeb\Contract\Simple\SimplePaymentServiceInterface;
use ItaliaMultimedia\XPayWeb\DataTransfer\Configuration;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\CreateHostedPaymentPageResponse;
use ItaliaMultimedia\XPayWeb\DataTransfer\Response\RetrieveOrderStatusResponse;
use ItaliaMultimedia\XPayWeb\Factory\PaymentOperationFactory;
use ItaliaMultimedia\XPayWeb\Service\AbstractPaymentService;
use Override;
use UnexpectedValueException;

use function is_array;
use function rawurlencode;
use function sprintf;

abstract class AbstractSimplePaymentService extends AbstractPaymentService implements SimplePaymentServiceInterface
{
    #[Override]
    public function getHostedPaymentPageApiUrl(): string
    {
        return sprintf(
            '%s%s',
            $this->getApiBaseUrl(),
            Configuration::HOSTED_PAYMENT_PAGE_API_ENDPOINT,
        );
    }

    #[Override]
    public function getOrderApiUrl(string $orderId): string
    {
        return sprintf(
            '%s%s',
            $this->getApiBaseUrl(),
            sprintf(Configuration::ORDER_API_ENDPOINT, rawurlencode($orderId)),
        );
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $responseBodyAsArray
     */
    protected function createRetrieveOrderStatusResponse(array $responseBodyAsArray): RetrieveOrderStatusResponse
    {
        $orderStatus = $this->getArray($responseBodyAsArray, 'orderStatus');
        $order = $this->getArray($orderStatus, 'order');
        $nonEmptyDataExtractionService = $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService();
        $dataExtractionService = $this->dataExtractionContainer->getLooseArrayDataExtractionService();

        return new RetrieveOrderStatusResponse(
            $nonEmptyDataExtractionService->getNonEmptyString($order, 'orderId'),
            $nonEmptyDataExtractionService->getNonEmptyString($order, 'amount'),
            $nonEmptyDataExtractionService->getNonEmptyString($order, 'currency'),
            $dataExtractionService->getNullableString($orderStatus, 'authorizedAmount'),
            $dataExtractionService->getNullableString($orderStatus, 'capturedAmount'),
            $dataExtractionService->getNullableString($orderStatus, 'lastOperationType'),
            $dataExtractionService->getNullableString($orderStatus, 'lastOperationTime'),
            $this->createPaymentOperations($this->getArray($orderStatus, 'operations')),
            $orderStatus,
        );
    }

    /**
     * @phpcs:ignore SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $responseBodyAsArray
     */
    protected function createHostedPaymentPageResponse(array $responseBodyAsArray): CreateHostedPaymentPageResponse
    {
        $nonEmptyDataExtractionService = $this->dataExtractionContainer->getLooseArrayNonEmptyDataExtractionService();

        return new CreateHostedPaymentPageResponse(
            $nonEmptyDataExtractionService->getNonEmptyString($responseBodyAsArray, 'hostedPage'),
            $nonEmptyDataExtractionService->getNonEmptyString($responseBodyAsArray, 'securityToken'),
        );
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $operations
     * @return array<\ItaliaMultimedia\XPayWeb\DataTransfer\PaymentOperation>
     * @phpcs:enable
     */
    private function createPaymentOperations(array $operations): array
    {
        $paymentOperationFactory = new PaymentOperationFactory($this->dataExtractionContainer);
        $paymentOperations = [];
        foreach ($operations as $operation) {
            if (!is_array($operation)) {
                throw new UnexpectedValueException('Invalid "operations" data.');
            }

            $paymentOperations[] = $paymentOperationFactory->create($operation);
        }

        return $paymentOperations;
    }

    /**
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     * @param array<mixed> $data
     * @return array<mixed>
     * @phpcs:enable
     */
    private function getArray(array $data, string $key): array
    {
        $arrayStorageService = $this->dataExtractionContainer->getArrayStorageService();
        if ($arrayStorageService !== null) {
            return $arrayStorageService->getArray($data, $arrayStorageService->parseKey($key));
        }

        $value = $data[$key] ?? [];
        if (!is_array($value)) {
            return [];
        }

        return $value;
    }
}
