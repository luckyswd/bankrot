<?php

declare(strict_types=1);

namespace App\Tests\Service\Templates;

use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Entity\Court;
use App\Entity\Creditor;

final class RegisterContractFactory
{
    public const string SBERBANK = 'ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО «СБЕРБАНК РОССИИ»';
    public const string VTB = 'БАНК ВТБ (ПУБЛИЧНОЕ АКЦИОНЕРНОЕ ОБЩЕСТВО)';

    public static function create(): Contracts
    {
        $contract = (new Contracts())
            ->setLastName('Терехова')
            ->setFirstName('Светлана')
            ->setMiddleName('Александровна')
            ->setLastNameGenitive('Тереховой')
            ->setFirstNameGenitive('Светланы')
            ->setMiddleNameGenitive('Александровны')
            ->setBirthDate(new \DateTime('1982-10-23'))
            ->setBirthPlace('гор. Ленинград')
            ->setSnils('141-362-038 17')
            ->setInn('782609400697')
            ->setPostalCode('196233')
            ->setRegistrationCity('Санкт-Петербург')
            ->setRegistrationStreet('Савушкина')
            ->setRegistrationHouse('18')
            ->setRegistrationApartment('18')
            ->setCaseNumber('А56-117152/2023')
            ->setCourt((new Court())->setName('Арбитражный суд города Санкт-Петербурга и Ленинградской области'))
            ->setProcedureInitiationKommersantPublicationDate(new \DateTime('2025-05-17'));

        $vtbClaim = (new ContractsCreditorsClaim())
            ->setCreditor((new Creditor())
                ->setName(self::VTB)
                ->setAddress('191144, Г. САНКТ-ПЕТЕРБУРГ, ДЕГТЯРНЫЙ ПЕР., Д. 11, ЛИТ. А')
                ->setHeadFullName('КОСТИН АНДРЕЙ ЛЕОНИДОВИЧ')
                ->setBankDetails('БИК 044525187, ИНН 7702070139, КПП 783501001'))
            ->setRegistryEntryDate(new \DateTime('2025-07-29'))
            ->setObligationType('Кредит')
            ->setBasis([
                ['number' => '625/0055-123', 'date' => '2020-02-01'],
                ['number' => '633/0055-456', 'date' => '2021-04-03'],
            ])
            ->setOriginDate(new \DateTime('2020-02-01'))
            ->setJudicialActDate(new \DateTime('2025-07-25'))
            ->setPrincipalAmount('1 500 000,00')
            ->setLateFee('1 000 000,00');

        $sberbankClaim = (new ContractsCreditorsClaim())
            ->setCreditor((new Creditor())
                ->setName(self::SBERBANK)
                ->setAddress('117997, Г. МОСКВА, УЛ. ВАВИЛОВА, Д. 19')
                ->setHeadFullName('ГРЕФ ГЕРМАН ОСКАРОВИЧ')
                ->setBankDetails('БИК 044525225, ИНН 7707083893, КПП 773601001'))
            ->setRegistryEntryDate(new \DateTime('2025-07-21'))
            ->setObligationType('Кредит')
            ->setBasis([['number' => '93-12345', 'date' => '2019-03-15']])
            ->setOriginDate(new \DateTime('2019-03-15'))
            ->setJudicialActDate(new \DateTime('2025-07-14'))
            ->setDisputeNumber('А56-117152/2023/тр.1')
            ->setPrincipalAmount('2 000 000,00')
            ->setInterest('116 862,59')
            ->setPenalty('1 206 637,98')
            ->setRepaidAmount('0.00');

        return $contract
            ->addCreditorsClaim($vtbClaim)
            ->addCreditorsClaim($sberbankClaim);
    }

    public static function claimOf(Contracts $contract, string $creditorName): ContractsCreditorsClaim
    {
        foreach ($contract->getCreditorsClaims() as $claim) {
            if ($claim->getCreditor()->getName() === $creditorName) {
                return $claim;
            }
        }

        throw new \LogicException('В тестовом деле нет требования кредитора ' . $creditorName);
    }
}
