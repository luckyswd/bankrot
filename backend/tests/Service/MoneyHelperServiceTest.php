<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\MoneyHelperService;
use PHPUnit\Framework\TestCase;

class MoneyHelperServiceTest extends TestCase
{
    private const string NBSP = "\u{00A0}";

    public function testThousandsAreSeparated(): void
    {
        $this->assertSame('7' . self::NBSP . '866,05', MoneyHelperService::format(amount: '7866.05'));
    }

    public function testSmallAmount(): void
    {
        $this->assertSame('560,55', MoneyHelperService::format(amount: '560.55'));
    }

    public function testNegativeAmount(): void
    {
        $this->assertSame('-10,00', MoneyHelperService::format(amount: '-10.00'));
    }

    public function testEmptyAmountIsZero(): void
    {
        $this->assertSame('0,00', MoneyHelperService::format(amount: null));
    }

    public function testMillions(): void
    {
        $this->assertSame('1' . self::NBSP . '253' . self::NBSP . '453,82', MoneyHelperService::format(amount: '1253453.82'));
    }

    public function testSubtract(): void
    {
        $this->assertSame('530.88', MoneyHelperService::subtract(minuend: '560.55', subtrahend: '29.67'));
    }
}
