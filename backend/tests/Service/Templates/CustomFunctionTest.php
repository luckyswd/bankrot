<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Service\DateHelperService;
use App\Service\Templates\CustomFunction;
use PHPUnit\Framework\TestCase;

class CustomFunctionTest extends TestCase
{
    private const string CURRENT_DATE = 'ТЕКУЩАЯ_ДАТА';

    private CustomFunction $customFunction;

    protected function setUp(): void
    {
        $this->customFunction = new CustomFunction();
    }

    public function testGenitiveMonthWithGuillemets(): void
    {
        $today = new \DateTimeImmutable();
        $expected = sprintf('«%s» %s %s', $today->format('d'), DateHelperService::getMonthNameGenitive(monthNumber: $today->format('n')), $today->format('Y'));

        $this->assertSame($expected, $this->customFunction->execute(functionName: self::CURRENT_DATE, parameters: ['«дд» ММММР гггг']));
    }

    public function testGenitiveMonthWithoutGuillemets(): void
    {
        $today = new \DateTimeImmutable();
        $expected = sprintf('%s %s %s', $today->format('d'), DateHelperService::getMonthNameGenitive(monthNumber: $today->format('n')), $today->format('Y'));

        $this->assertSame($expected, $this->customFunction->execute(functionName: self::CURRENT_DATE, parameters: ['дд ММММР гггг']));
    }

    public function testNominativeMonthIsUnchanged(): void
    {
        $nominativeMonths = [1 => 'январь', 2 => 'февраль', 3 => 'март', 4 => 'апрель', 5 => 'май', 6 => 'июнь', 7 => 'июль', 8 => 'август', 9 => 'сентябрь', 10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь'];
        $today = new \DateTimeImmutable();
        $expected = sprintf('%s %s', $nominativeMonths[(int)$today->format('n')], $today->format('Y'));

        $this->assertSame($expected, $this->customFunction->execute(functionName: self::CURRENT_DATE, parameters: ['ММММ гггг']));
    }

    public function testNumericMaskIsUnchanged(): void
    {
        $this->assertSame(
            (new \DateTimeImmutable())->format('d.m.Y'),
            $this->customFunction->execute(functionName: self::CURRENT_DATE, parameters: ['дд.ММ.гггг']),
        );
    }
}
