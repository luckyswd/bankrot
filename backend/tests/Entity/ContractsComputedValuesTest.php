<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Contracts;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContractsComputedValuesTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function registryClosingDates(): array
    {
        return [
            'пример заказчика' => ['2026-04-08', '2026-06-08'],
            'в конечном месяце нет такого числа' => ['2025-12-31', '2026-02-28'],
            'обычная дата' => ['2025-02-07', '2025-04-07'],
            'високосный февраль' => ['2023-12-31', '2024-02-29'],
        ];
    }

    #[DataProvider('registryClosingDates')]
    public function testRegistryClosingDateIsTwoMonthsAfterKommersantPublication(string $publicationDate, string $expectedClosingDate): void
    {
        $contract = (new Contracts())->setProcedureInitiationKommersantPublicationDate(new \DateTime($publicationDate));

        $this->assertSame($expectedClosingDate, $contract->getRegistryClosingDate()?->format('Y-m-d'));
    }

    public function testRegistryClosingDateIsEmptyWithoutPublication(): void
    {
        $this->assertNull((new Contracts())->getRegistryClosingDate());
    }

    public function testClaimsConsideredCountIsSumOfIncludedAndRejected(): void
    {
        $contract = (new Contracts())->setClaimsIncludedCount(2)->setClaimsRejectedCount(0);

        $this->assertSame(2, $contract->getClaimsConsideredCount());
    }

    public function testClaimsConsideredCountIsEmptyWithoutCounts(): void
    {
        $this->assertNull((new Contracts())->getClaimsConsideredCount());
    }

    public function testUnpaidExpensesAreCalculatedInKopecks(): void
    {
        $contract = (new Contracts())->setEfrsbExpensesAmount('560.55')->setEfrsbExpensesPaid('29.67');

        $this->assertSame('530.88', $contract->getEfrsbExpensesUnpaid());
    }

    public function testUnpaidIsNegativeWhenPaidExceedsAmount(): void
    {
        $contract = (new Contracts())->setPostalExpensesAmount('1490.00')->setPostalExpensesPaid('1500.00');

        $this->assertSame('-10.00', $contract->getPostalExpensesUnpaid());
    }

    public function testNewspaperExpensesComeFromKommersantPublicationCost(): void
    {
        $contract = (new Contracts())->setProcedureInitiationKommersantPublicationCost('7866.05');

        $this->assertSame('7866.05', $contract->getNewspaperExpensesAmount());
        $this->assertSame('7866.05', $contract->getNewspaperExpensesUnpaid());
    }

    public function testUnpaidIsEmptyWithoutAmountAndPaid(): void
    {
        $this->assertNull((new Contracts())->getFutureEfrsbExpensesUnpaid());
    }
}
