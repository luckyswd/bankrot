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

    public function testParseAmountWithSpacesAndComma(): void
    {
        $this->assertSame('363300.48', MoneyHelperService::parse(amount: '363 300,48'));
    }

    public function testParseWholeRubles(): void
    {
        $this->assertSame('1490.00', MoneyHelperService::parse(amount: '1490'));
    }

    public function testParseSpacesWithDot(): void
    {
        $this->assertSame('1490.00', MoneyHelperService::parse(amount: '1 490.00'));
    }

    public function testParseNonBreakingSpace(): void
    {
        $this->assertSame('7866.05', MoneyHelperService::parse(amount: '7' . self::NBSP . '866,05'));
    }

    public function testParseSingleKopeckDigit(): void
    {
        $this->assertSame('1490.50', MoneyHelperService::parse(amount: '1490,5'));
    }

    public function testUnparseableAmountIsZero(): void
    {
        $this->assertSame('0.00', MoneyHelperService::parse(amount: 'около 300'));
    }

    public function testNormalizeReturnsNullForUnparseableAndEmpty(): void
    {
        $this->assertNull(MoneyHelperService::normalize(amount: 'около 300'));
        $this->assertNull(MoneyHelperService::normalize(amount: ''));
        $this->assertNull(MoneyHelperService::normalize(amount: null));
    }

    public function testFormatPlainWithoutThousandsSeparator(): void
    {
        $this->assertSame('3616862,59', MoneyHelperService::formatPlain(amount: '3616862.59'));
    }

    public function testFormatPlainEmptyIsZero(): void
    {
        $this->assertSame('0,00', MoneyHelperService::formatPlain(amount: null));
    }

    public function testAddSumsTextAmounts(): void
    {
        $this->assertSame('2116862.59', MoneyHelperService::add('2 000 000,00', '116862.59', null, ''));
    }

    public function testMinAndMax(): void
    {
        $this->assertSame('2116862.59', MoneyHelperService::min(first: '2200000.00', second: '2116862.59'));
        $this->assertSame('0.00', MoneyHelperService::max(first: '-83137.41', second: '0'));
    }

    public function testPercentIsRoundedToHundredths(): void
    {
        $this->assertSame('58,53', MoneyHelperService::percent(part: '2116862.59', total: '3616862.59'));
        $this->assertSame('3,77', MoneyHelperService::percent(part: '83137.41', total: '2206637.98'));
    }

    public function testPercentOfZeroTotalIsZero(): void
    {
        $this->assertSame('0,00', MoneyHelperService::percent(part: '100.00', total: '0.00'));
    }

    public function testFullRepaymentIsHundredPercent(): void
    {
        $this->assertSame('100,00', MoneyHelperService::percent(part: '3616862.59', total: '3616862.59'));
    }
}
