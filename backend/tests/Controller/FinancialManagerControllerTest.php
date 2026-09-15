<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\Test\TestUserFixtures;
use App\Entity\FinancialManager;
use App\Tests\BaseTestCase;

class FinancialManagerControllerTest extends BaseTestCase
{
    private const string FIO_PREFIX = 'Полисный Тест';
    private const string POLICY_NUMBER = 'Arbitr-3980975400-26366';

    public function setUp(): void
    {
        parent::setUp();

        $this->addFixtures(classes: [TestUserFixtures::class]);
        $this->executeFixtures();

        $token = $this->getAuthToken(user: $this->getUser(reference: 'user1'));
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
    }

    public function testCreateFinancialManagerWithInsurancePolicy(): void
    {
        $this->client->request(
            method: 'POST',
            uri: '/api/v1/financial-managers',
            content: json_encode([
                'fio' => self::FIO_PREFIX . ' Создание',
                'insuranceContractNumber' => self::POLICY_NUMBER,
                'insuranceContractDate' => '2022-09-08',
                'insuranceStartDate' => '2022-09-09',
                'insuranceEndDate' => '2026-09-08',
            ]),
        );

        $this->assertResponseStatusCodeSame(expectedCode: 201);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertSame(self::POLICY_NUMBER, $response['insuranceContractNumber']);
        $this->assertSame('2022-09-08', $response['insuranceContractDate']);
        $this->assertSame('2022-09-09', $response['insuranceStartDate']);
        $this->assertSame('2026-09-08', $response['insuranceEndDate']);
        $this->assertArrayHasKey('insuranceStatus', $response);

        $financialManager = self::$em->getRepository(FinancialManager::class)->find($response['id']);
        self::$em->refresh($financialManager);

        $this->assertSame(self::POLICY_NUMBER, $financialManager->getInsuranceContractNumber());
        $this->assertSame('2026-09-08', $financialManager->getInsuranceEndDate()?->format('Y-m-d'));
    }

    public function testClearingPolicyNumberKeepsOtherPolicyFields(): void
    {
        $financialManager = $this->persistFinancialManager(
            fio: self::FIO_PREFIX . ' Очистка',
            insuranceEndDate: new \DateTime('2026-09-08'),
        );

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/financial-managers/' . $financialManager->getId(),
            content: json_encode(['insuranceContractNumber' => '']),
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertNull($response['insuranceContractNumber']);
        $this->assertSame('2026-09-08', $response['insuranceEndDate']);
        $this->assertSame('2022-09-09', $response['insuranceStartDate']);
    }

    public function testInsuranceStatusInList(): void
    {
        $this->persistFinancialManager(
            fio: self::FIO_PREFIX . ' Истёк',
            insuranceEndDate: new \DateTime('yesterday'),
        );
        $this->persistFinancialManager(
            fio: self::FIO_PREFIX . ' Последний день',
            insuranceEndDate: new \DateTime('today'),
        );
        $this->persistFinancialManager(
            fio: self::FIO_PREFIX . ' Без полиса',
            insuranceEndDate: null,
        );

        $this->client->request(
            method: 'GET',
            uri: '/api/v1/financial-managers?limit=all&search=' . urlencode(self::FIO_PREFIX),
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);
        $statuses = array_column(array: $response['items'], column_key: 'insuranceStatus', index_key: 'fio');

        $this->assertSame('expired', $statuses[self::FIO_PREFIX . ' Истёк']);
        $this->assertSame('valid', $statuses[self::FIO_PREFIX . ' Последний день']);
        $this->assertSame('missing', $statuses[self::FIO_PREFIX . ' Без полиса']);
    }

    public function testInvalidPolicyDateIsRejected(): void
    {
        $financialManager = $this->persistFinancialManager(
            fio: self::FIO_PREFIX . ' Неверная дата',
            insuranceEndDate: new \DateTime('2026-09-08'),
        );

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/financial-managers/' . $financialManager->getId(),
            content: json_encode(['insuranceEndDate' => '08.09.2026']),
        );

        $this->assertResponseStatusCodeSame(expectedCode: 400);

        self::$em->refresh($financialManager);
        $this->assertSame('2026-09-08', $financialManager->getInsuranceEndDate()?->format('Y-m-d'));
    }

    private function persistFinancialManager(string $fio, ?\DateTimeInterface $insuranceEndDate): FinancialManager
    {
        $financialManager = (new FinancialManager())
            ->setFio($fio)
            ->setInsuranceContractNumber(self::POLICY_NUMBER)
            ->setInsuranceStartDate(new \DateTime('2022-09-09'))
            ->setInsuranceEndDate($insuranceEndDate);

        self::$em->persist($financialManager);
        self::$em->flush();

        return $financialManager;
    }
}
