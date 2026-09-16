<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\Test\TestUserFixtures;
use App\Entity\Contracts;
use App\Entity\ContractsCreditorsClaim;
use App\Entity\Creditor;
use App\Service\MoneyHelperService;
use App\Tests\BaseTestCase;

class ContractsCreditorsClaimsControllerTest extends BaseTestCase
{
    private const string CONTRACT_NUMBER = 'CONTRACT-001';
    private const string DATE_FORMAT = 'Y-m-d';
    private const array REGISTER_GETTER_KEYS = [
        'registryMainClaims',
        'registrySanctionClaims',
        'registryMainCreditorsCount',
        'registryMainClaimsCount',
        'registryMainAmountText',
        'registryMainRepaidAmountText',
        'registryMainRepaidPercentText',
        'registryOpeningDateShort',
        'registryClosingDateShort',
        'registrationAddressWithPostalCode',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->addFixtures(classes: [TestUserFixtures::class]);
        $this->executeFixtures();

        $token = $this->getAuthToken(user: $this->getUser(reference: 'user1'));
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
    }

    public function testRegistryFieldsAreSaved(): void
    {
        $creditor = $this->createCreditor();

        $response = $this->updateClaims(claims: [[
            'creditorId' => $creditor->getId(),
            'registryEntryDate' => '2025-07-21',
            'obligationType' => 'Кредит',
            'disputeNumber' => 'А56-117152/2023/тр.1',
            'originDate' => '2019-03-15',
            'repaidAmount' => '0.00',
        ]]);

        $returnedClaim = $response['judicial_procedure']['creditorsClaims'][0];
        $this->assertStringStartsWith('2025-07-21', $returnedClaim['registryEntryDate']);
        $this->assertSame('Кредит', $returnedClaim['obligationType']);
        $this->assertSame('А56-117152/2023/тр.1', $returnedClaim['disputeNumber']);
        $this->assertStringStartsWith('2019-03-15', $returnedClaim['originDate']);
        $this->assertSame('0.00', MoneyHelperService::normalize(amount: $returnedClaim['repaidAmount']));

        $claim = $this->findClaim(creditor: $creditor);
        $this->assertSame('2025-07-21', $claim->getRegistryEntryDate()?->format(self::DATE_FORMAT));
        $this->assertSame('Кредит', $claim->getObligationType());
        $this->assertSame('А56-117152/2023/тр.1', $claim->getDisputeNumber());
        $this->assertSame('2019-03-15', $claim->getOriginDate()?->format(self::DATE_FORMAT));
        $this->assertSame('0.00', MoneyHelperService::normalize(amount: $claim->getRepaidAmount()));
    }

    public function testEmptyRegistryEntryDateClearsOnlyThisField(): void
    {
        $existingClaim = $this->createClaimWithRegistryFields();

        $this->updateClaims(claims: [[
            'id' => $existingClaim->getId(),
            'creditorId' => $existingClaim->getCreditor()->getId(),
            'registryEntryDate' => '',
        ]]);

        $claim = $this->findClaim(creditor: $existingClaim->getCreditor());
        $this->assertNull($claim->getRegistryEntryDate());
        $this->assertSame('Кредит', $claim->getObligationType());
        $this->assertSame('А56-117152/2023/тр.1', $claim->getDisputeNumber());
        $this->assertSame('2019-03-15', $claim->getOriginDate()?->format(self::DATE_FORMAT));
        $this->assertSame('10.00', MoneyHelperService::normalize(amount: $claim->getRepaidAmount()));
    }

    public function testRepaidAmountWithSpacesAndCommaIsNormalized(): void
    {
        $creditor = $this->createCreditor();

        $this->updateClaims(claims: [[
            'creditorId' => $creditor->getId(),
            'repaidAmount' => '1 490,50',
        ]]);

        $this->assertSame('1490.50', MoneyHelperService::normalize(amount: $this->findClaim(creditor: $creditor)->getRepaidAmount()));
    }

    public function testUnparseableValuesKeepStoredRegistryFields(): void
    {
        $existingClaim = $this->createClaimWithRegistryFields();

        $this->updateClaims(claims: [[
            'id' => $existingClaim->getId(),
            'creditorId' => $existingClaim->getCreditor()->getId(),
            'registryEntryDate' => '2025-07-21T00:00:00+00:00',
            'originDate' => '15.03.2019',
            'repaidAmount' => 'около 300',
        ]]);

        $claim = $this->findClaim(creditor: $existingClaim->getCreditor());
        $this->assertSame('2025-07-21', $claim->getRegistryEntryDate()?->format(self::DATE_FORMAT));
        $this->assertSame('2019-03-15', $claim->getOriginDate()?->format(self::DATE_FORMAT));
        $this->assertSame('10.00', MoneyHelperService::normalize(amount: $claim->getRepaidAmount()));
    }

    public function testRegisterGettersAreNotSerialized(): void
    {
        $this->createClaimWithRegistryFields();

        $this->client->request(method: 'GET', uri: '/api/v1/contracts/' . $this->findContract()->getId());

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        foreach ($response as $stage) {
            if (!is_array($stage)) {
                continue;
            }

            foreach (self::REGISTER_GETTER_KEYS as $key) {
                $this->assertArrayNotHasKey($key, $stage);
            }
        }
    }

    private function createCreditor(): Creditor
    {
        $creditor = new Creditor();
        $creditor->setName('ПАО «Сбербанк России»');
        self::$em->persist($creditor);
        self::$em->flush();

        return $creditor;
    }

    private function createClaimWithRegistryFields(): ContractsCreditorsClaim
    {
        $claim = new ContractsCreditorsClaim();
        $claim->setCreditor($this->createCreditor());
        $claim->setRegistryEntryDate(new \DateTime('2025-07-21'));
        $claim->setObligationType('Кредит');
        $claim->setDisputeNumber('А56-117152/2023/тр.1');
        $claim->setOriginDate(new \DateTime('2019-03-15'));
        $claim->setRepaidAmount('10.00');

        $this->findContract()->addCreditorsClaim($claim);
        self::$em->persist($claim);
        self::$em->flush();

        return $claim;
    }

    /**
     * @param array<int, array<string, mixed>> $claims
     *
     * @return array<string, array<string, mixed>>
     */
    private function updateClaims(array $claims): array
    {
        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/contracts/' . $this->findContract()->getId(),
            content: json_encode(['judicial_procedure' => ['creditorsClaims' => $claims]]),
        );

        $this->assertResponseIsSuccessful();

        return json_decode(json: $this->client->getResponse()->getContent(), associative: true);
    }

    private function findClaim(Creditor $creditor): ContractsCreditorsClaim
    {
        self::$em->clear();

        $claim = self::$em->getRepository(ContractsCreditorsClaim::class)->findOneBy([
            'contract' => $this->findContract(),
            'creditor' => $creditor->getId(),
        ]);
        $this->assertNotNull($claim);

        return $claim;
    }

    private function findContract(): Contracts
    {
        $contract = self::$em->getRepository(Contracts::class)->findOneBy(['contractNumber' => self::CONTRACT_NUMBER]);
        $this->assertNotNull($contract);

        return $contract;
    }
}
