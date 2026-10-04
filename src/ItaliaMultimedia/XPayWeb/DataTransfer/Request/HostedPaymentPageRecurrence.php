<?php

declare(strict_types=1);

namespace ItaliaMultimedia\XPayWeb\DataTransfer\Request;

use WebServCo\Data\Contract\Transfer\DataTransferInterface;

final readonly class HostedPaymentPageRecurrence implements DataTransferInterface
{
    public const string ACTION_CONTRACT_CREATION = 'CONTRACT_CREATION';

    public const string CONTRACT_TYPE_MIT_SCHEDULED = 'MIT_SCHEDULED';

    public const string CONTRACT_TYPE_MIT_UNSCHEDULED = 'MIT_UNSCHEDULED';

    public function __construct(
        public string $contractId,
        public string $contractType,
        public ?string $contractExpiryDate = null,
        public ?string $contractFrequency = null,
        public string $action = self::ACTION_CONTRACT_CREATION,
    ) {
    }

    public static function createMitScheduled(
        string $contractId,
        ?string $contractExpiryDate = null,
        ?string $contractFrequency = null,
    ): self {
        return new self($contractId, self::CONTRACT_TYPE_MIT_SCHEDULED, $contractExpiryDate, $contractFrequency);
    }

    public static function createMitUnscheduled(string $contractId): self
    {
        return new self($contractId, self::CONTRACT_TYPE_MIT_UNSCHEDULED);
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        $data = [
            'action' => $this->action,
        ];

        if ($this->contractExpiryDate !== null) {
            $data['contractExpiryDate'] = $this->contractExpiryDate;
        }

        if ($this->contractFrequency !== null) {
            $data['contractFrequency'] = $this->contractFrequency;
        }

        $data['contractId'] = $this->contractId;
        $data['contractType'] = $this->contractType;

        return $data;
    }
}
