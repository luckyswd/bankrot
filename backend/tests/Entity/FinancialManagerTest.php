<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Enum\InsuranceStatus;
use App\Entity\FinancialManager;
use PHPUnit\Framework\TestCase;

class FinancialManagerTest extends TestCase
{
    private const string POLICY_NUMBER = 'Arbitr-3980975400-26366';

    public function testInsuranceIsValidOnLastDayOfPolicy(): void
    {
        $financialManager = (new FinancialManager())->setInsuranceEndDate(new \DateTime('2026-09-08'));

        $status = $financialManager->getInsuranceStatus(today: new \DateTimeImmutable('2026-09-08 18:30'));

        $this->assertSame(InsuranceStatus::VALID, $status);
    }

    public function testInsuranceIsExpiredNextDayAfterPolicyEnds(): void
    {
        $financialManager = (new FinancialManager())->setInsuranceEndDate(new \DateTime('2026-09-08'));

        $status = $financialManager->getInsuranceStatus(today: new \DateTimeImmutable('2026-09-09'));

        $this->assertSame(InsuranceStatus::EXPIRED, $status);
    }

    public function testInsuranceIsMissingWithoutEndDate(): void
    {
        $financialManager = (new FinancialManager())
            ->setInsuranceContractNumber(self::POLICY_NUMBER)
            ->setInsuranceStartDate(new \DateTime('2022-09-09'));

        $status = $financialManager->getInsuranceStatus(today: new \DateTimeImmutable('2026-09-09'));

        $this->assertSame(InsuranceStatus::MISSING, $status);
    }

    public function testFullInsuranceContractDescription(): void
    {
        $financialManager = (new FinancialManager())
            ->setInsuranceContractNumber(self::POLICY_NUMBER)
            ->setInsuranceContractDate(new \DateTime('2022-09-08'))
            ->setInsuranceStartDate(new \DateTime('2022-09-09'))
            ->setInsuranceEndDate(new \DateTime('2026-09-08'));

        $this->assertSame(
            'Arbitr-3980975400-26366 от 08.09.2022 г., срок действия с 09.09.2022 г. по 08.09.2026 г.',
            $financialManager->getInsuranceContractDescription(),
        );
    }

    public function testInsuranceContractDescriptionWithoutContractDate(): void
    {
        $financialManager = (new FinancialManager())
            ->setInsuranceContractNumber(self::POLICY_NUMBER)
            ->setInsuranceStartDate(new \DateTime('2022-09-09'))
            ->setInsuranceEndDate(new \DateTime('2026-09-08'));

        $this->assertSame(
            'Arbitr-3980975400-26366, срок действия с 09.09.2022 г. по 08.09.2026 г.',
            $financialManager->getInsuranceContractDescription(),
        );
    }

    public function testInsuranceContractDescriptionIsEmptyWithoutPolicy(): void
    {
        $this->assertSame('', (new FinancialManager())->getInsuranceContractDescription());
    }
}
