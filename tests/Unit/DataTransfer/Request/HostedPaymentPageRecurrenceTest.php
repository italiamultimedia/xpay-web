<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransfer\Request;

use ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence;
use PHPUnit\Framework\TestCase;

final class HostedPaymentPageRecurrenceTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence::__construct
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence::toArray
     */
    public function testToArrayReturnsUnscheduledContractCreationPayload(): void
    {
        $recurrence = new HostedPaymentPageRecurrence(
            'CONTRACT-123',
            HostedPaymentPageRecurrence::CONTRACT_TYPE_MIT_UNSCHEDULED,
        );

        self::assertSame(
            [
                'action' => HostedPaymentPageRecurrence::ACTION_CONTRACT_CREATION,
                'contractId' => 'CONTRACT-123',
                'contractType' => HostedPaymentPageRecurrence::CONTRACT_TYPE_MIT_UNSCHEDULED,
            ],
            $recurrence->toArray(),
        );
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence::__construct
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Request\HostedPaymentPageRecurrence::toArray
     */
    public function testToArrayReturnsScheduledContractCreationPayload(): void
    {
        $recurrence = new HostedPaymentPageRecurrence(
            'CONTRACT-123',
            HostedPaymentPageRecurrence::CONTRACT_TYPE_MIT_SCHEDULED,
            '2027-12-31',
            '30',
        );

        self::assertSame(
            [
                'action' => HostedPaymentPageRecurrence::ACTION_CONTRACT_CREATION,
                'contractExpiryDate' => '2027-12-31',
                'contractFrequency' => '30',
                'contractId' => 'CONTRACT-123',
                'contractType' => HostedPaymentPageRecurrence::CONTRACT_TYPE_MIT_SCHEDULED,
            ],
            $recurrence->toArray(),
        );
    }
}
