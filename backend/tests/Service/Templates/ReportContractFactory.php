<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Entity\ContractsPreCourtCreditor;
use App\Entity\Court;
use App\Entity\Creditor;
use App\Entity\Enum\ProcedureExtensionStatus;
use App\Entity\FinancialManager;

final class ReportContractFactory
{
    public static function create(): Contracts
    {
        $financialManager = (new FinancialManager())
            ->setFio('Федорец Василий Владимирович')
            ->setInsuranceContractNumber('Arbitr-3980975400-26366')
            ->setInsuranceContractDate(new \DateTime('2022-09-08'))
            ->setInsuranceStartDate(new \DateTime('2022-09-09'))
            ->setInsuranceEndDate(new \DateTime('2026-09-08'));

        $court = (new Court())->setName('Арбитражный суд города Санкт-Петербурга и Ленинградской области');
        $sberbank = (new Creditor())->setName('Публичное акционерное общество "Сбербанк России"');
        $alfaBank = (new Creditor())->setName('Акционерное общество "Альфа-Банк"');

        $contract = (new Contracts())
            ->setLastName('Иванова')
            ->setFirstName('Мария')
            ->setMiddleName('Петровна')
            ->setLastNameGenitive('Ивановой')
            ->setFirstNameGenitive('Марии')
            ->setMiddleNameGenitive('Петровны')
            ->setGender('female')
            ->setMaritalStatus('married_3y_ago')
            ->setIsLastNameChanged(true)
            ->setChangedLastName('Петрова Мария Петровна')
            ->setCaseNumber('А56-12578/2025')
            ->setCourt($court)
            ->setFinancialManager($financialManager)
            ->setProcedureInitiationDecisionDate(new \DateTime('2025-05-05'))
            ->changeProcedureExtension(status: ProcedureExtensionStatus::EXTENDED, dates: ['2026-03-12', '2026-09-10'])
            ->setPropertyInventoryDate(new \DateTime('2025-07-02'))
            ->setZagsCertificatePeriodFrom(new \DateTime('2009-02-11'))
            ->setZagsCertificatePeriodTo(new \DateTime('2025-07-10'))
            ->setProcedureInitiationKommersantIssueNumber('139 (7101)')
            ->setProcedureInitiationKommersantAdNumber('78230149776')
            ->setProcedureInitiationKommersantPublicationDate(new \DateTime('2025-02-07'))
            ->setProcedureInitiationKommersantPublicationCost('7866.05')
            ->setProcedureInitiationEfrsbMessageNumber('3134699')
            ->setProcedureInitiationEfrsbMessageDate(new \DateTime('2025-02-18'))
            ->setProcedureInitiationCreditorsNotificationDate(new \DateTime('2025-02-20'))
            ->setClaimsIncludedCount(2)
            ->setClaimsRejectedCount(0)
            ->setEfrsbExpensesAmount('560.55')
            ->setEfrsbExpensesPaid('29.67')
            ->setPostalExpensesAmount('1490.00')
            ->setFutureEfrsbExpensesAmount('1121.10')
            ->setBankruptcySignsEfrsbPublicationDate(new \DateTime('2026-05-14'))
            ->setChildren([
                ['lastName' => 'Иванова', 'firstName' => 'Анна', 'middleName' => 'Ивановна', 'birthDate' => '2015-03-20'],
                ['lastName' => 'Иванов', 'firstName' => 'Пётр', 'middleName' => 'Иванович', 'birthDate' => '2011-07-18'],
            ]);

        foreach ([$sberbank, $alfaBank] as $creditor) {
            $contract->addPreCourtCreditor((new ContractsPreCourtCreditor())->setCreditor($creditor));
            $contract->addCreditorsClaim((new ContractsCreditorsClaim())->setCreditor($creditor));
        }

        return $contract;
    }
}
