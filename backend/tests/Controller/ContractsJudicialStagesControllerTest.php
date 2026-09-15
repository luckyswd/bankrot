<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\Test\TestUserFixtures;
use App\Entity\Contracts;
use App\Entity\Enum\ProcedureExtensionStatus;
use App\Tests\BaseTestCase;

class ContractsJudicialStagesControllerTest extends BaseTestCase
{
    private const string CONTRACT_NUMBER = 'CONTRACT-001';

    public function setUp(): void
    {
        parent::setUp();

        $this->addFixtures(classes: [TestUserFixtures::class]);
        $this->executeFixtures();

        $token = $this->getAuthToken(user: $this->getUser(reference: 'user1'));
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
    }

    public function testPublicationsAndCreditorsNotificationAreSaved(): void
    {
        $response = $this->updateContract(data: [
            'judicial_procedure_initiation' => [
                'procedureInitiationEfrsbMessageNumber' => '3134699',
                'procedureInitiationEfrsbMessageDate' => '2025-02-18',
                'procedureInitiationKommersantIssueNumber' => '139 (7101)',
                'procedureInitiationKommersantAdNumber' => '78230149776',
                'procedureInitiationKommersantPublicationDate' => '2025-02-07',
                'procedureInitiationKommersantPublicationCost' => '7866.05',
                'procedureInitiationCreditorsNotificationDate' => '2025-02-10',
            ],
        ]);

        $initiation = $response['judicial_procedure_initiation'];

        $this->assertSame('3134699', $initiation['procedureInitiationEfrsbMessageNumber']);
        $this->assertStringStartsWith('2025-02-18', $initiation['procedureInitiationEfrsbMessageDate']);
        $this->assertSame('139 (7101)', $initiation['procedureInitiationKommersantIssueNumber']);
        $this->assertSame('78230149776', $initiation['procedureInitiationKommersantAdNumber']);
        $this->assertStringStartsWith('2025-02-07', $initiation['procedureInitiationKommersantPublicationDate']);
        $this->assertSame('7866.05', $initiation['procedureInitiationKommersantPublicationCost']);
        $this->assertStringStartsWith('2025-02-10', $initiation['procedureInitiationCreditorsNotificationDate']);
    }

    public function testEmptyPublicationValuesAreNotReturned(): void
    {
        $response = $this->updateContract(data: [
            'judicial_procedure_initiation' => [
                'procedureInitiationEfrsbMessageNumber' => '',
                'procedureInitiationKommersantPublicationDate' => '',
            ],
        ]);

        $this->assertArrayNotHasKey('procedureInitiationEfrsbMessageNumber', $response['judicial_procedure_initiation']);
        $this->assertArrayNotHasKey('procedureInitiationKommersantPublicationDate', $response['judicial_procedure_initiation']);
    }

    public function testRegistryClosingDateIsCalculatedAndCannotBeOverwritten(): void
    {
        $response = $this->updateContract(data: [
            'judicial_procedure_initiation' => [
                'procedureInitiationKommersantPublicationDate' => '2025-02-07',
                'registryClosingDate' => '2030-01-01',
            ],
        ]);

        $this->assertStringStartsWith('2025-04-07', $response['judicial_procedure_initiation']['registryClosingDate']);
    }

    public function testComputedProcedureValuesAreReturned(): void
    {
        $response = $this->updateContract(data: [
            'judicial_procedure_initiation' => [
                'procedureInitiationKommersantPublicationCost' => '7866.05',
            ],
            'judicial_procedure' => [
                'claimsIncludedCount' => 2,
                'claimsRejectedCount' => 0,
                'claimsConsideredCount' => 99,
                'efrsbExpensesAmount' => '560.55',
                'efrsbExpensesPaid' => '29.67',
                'efrsbExpensesUnpaid' => '1.00',
            ],
        ]);

        $procedure = $response['judicial_procedure'];

        $this->assertSame(2, $procedure['claimsConsideredCount']);
        $this->assertSame('530.88', $procedure['efrsbExpensesUnpaid']);
        $this->assertSame('7866.05', $procedure['newspaperExpensesAmount']);
        $this->assertSame('7866.05', $procedure['newspaperExpensesUnpaid']);
        $this->assertArrayNotHasKey('postalExpensesUnpaid', $procedure);
    }

    public function testProcedureExtensionWithTwoDates(): void
    {
        $response = $this->updateContract(data: [
            'judicial_procedure' => [
                'procedureExtensionStatus' => 'extended',
                'procedureExtensionDates' => ['2026-03-12', '2026-09-10'],
            ],
        ]);

        $this->assertSame('extended', $response['judicial_procedure']['procedureExtensionStatus']);
        $this->assertSame(['2026-03-12', '2026-09-10'], $response['judicial_procedure']['procedureExtensionDates']);
    }

    public function testChangingProcedureExtensionStatusRemovesDates(): void
    {
        $contract = $this->findContract();
        $contract->changeProcedureExtension(status: ProcedureExtensionStatus::EXTENDED, dates: ['2026-03-12']);
        self::$em->flush();

        $response = $this->updateContract(data: [
            'judicial_procedure' => [
                'procedureExtensionStatus' => 'not_extended',
                'procedureExtensionDates' => ['2026-03-12'],
            ],
        ]);

        $this->assertSame('not_extended', $response['judicial_procedure']['procedureExtensionStatus']);
        $this->assertArrayNotHasKey('procedureExtensionDates', $response['judicial_procedure']);
    }

    public function testProcedureDataIsSaved(): void
    {
        $response = $this->updateContract(data: [
            'judicial_procedure' => [
                'propertyInventoryDate' => '2025-07-02',
                'zagsDepartment' => 'Отдел ЗАГС администрации Всеволожского муниципального района Ленинградской области',
                'zagsCertificatePeriodFrom' => '2009-02-11',
                'zagsCertificatePeriodTo' => '2025-07-10',
                'claimsIncludedCount' => '2',
                'claimsRejectedCount' => 0,
                'efrsbExpensesAmount' => '560.55',
                'efrsbExpensesPaid' => '29.67',
                'postalExpensesAmount' => '1490.00',
                'newspaperExpensesPaid' => '',
                'futureEfrsbExpensesAmount' => '1121.10',
                'bankruptcySignsEfrsbPublicationDate' => '2026-05-14',
            ],
        ]);

        $procedure = $response['judicial_procedure'];

        $this->assertStringStartsWith('2025-07-02', $procedure['propertyInventoryDate']);
        $this->assertSame('Отдел ЗАГС администрации Всеволожского муниципального района Ленинградской области', $procedure['zagsDepartment']);
        $this->assertStringStartsWith('2009-02-11', $procedure['zagsCertificatePeriodFrom']);
        $this->assertStringStartsWith('2025-07-10', $procedure['zagsCertificatePeriodTo']);
        $this->assertSame(2, $procedure['claimsIncludedCount']);
        $this->assertSame(0, $procedure['claimsRejectedCount']);
        $this->assertSame('560.55', $procedure['efrsbExpensesAmount']);
        $this->assertSame('29.67', $procedure['efrsbExpensesPaid']);
        $this->assertSame('1490.00', $procedure['postalExpensesAmount']);
        $this->assertArrayNotHasKey('newspaperExpensesPaid', $procedure);
        $this->assertSame('1121.10', $procedure['futureEfrsbExpensesAmount']);
        $this->assertStringStartsWith('2026-05-14', $procedure['bankruptcySignsEfrsbPublicationDate']);
    }

    /**
     * @param array<string, array<string, mixed>> $data
     *
     * @return array<string, array<string, mixed>>
     */
    private function updateContract(array $data): array
    {
        $contract = $this->findContract();

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/contracts/' . $contract->getId(),
            content: json_encode($data),
        );

        $this->assertResponseIsSuccessful();

        return json_decode(json: $this->client->getResponse()->getContent(), associative: true);
    }

    private function findContract(): Contracts
    {
        $contract = self::$em->getRepository(Contracts::class)->findOneBy(['contractNumber' => self::CONTRACT_NUMBER]);
        $this->assertNotNull($contract);

        return $contract;
    }
}
