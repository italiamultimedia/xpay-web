<?php

declare(strict_types=1);

namespace Tests\Unit\DataTransfer\Result;

use ItaliaMultimedia\XPayWeb\DataTransfer\Result\HostedPaymentResult;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class HostedPaymentResultTest extends TestCase
{
    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Result\HostedPaymentResult::__construct
     */
    public function testConstructorAcceptsOrderId(): void
    {
        $result = new HostedPaymentResult('ORDER-123');

        self::assertSame('ORDER-123', $result->orderId);
    }

    /**
     * @covers \ItaliaMultimedia\XPayWeb\DataTransfer\Result\HostedPaymentResult::__construct
     */
    public function testConstructorRejectsEmptyOrderId(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Missing or invalid "orderId" data.');

        $result = new HostedPaymentResult('');
        self::fail($result::class . ' should not have been created.');
    }
}
