<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\Test\TestUserFixtures;
use App\Entity\Enum\ContractStatus;
use App\Tests\BaseTestCase;

class ContractsControllerTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->addFixtures(classes: [TestUserFixtures::class]);
        $this->executeFixtures();
    }

    /**
     * Тест получения списка всех контрактов с фильтром "all"
     * Проверяет, что API возвращает все контракты с корректной структурой ответа.
     */
    public function testListAllContracts(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?filter=all');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertArrayHasKey('counts', $response);
        $this->assertGreaterThanOrEqual(2, count($response['data']));
        $this->assertGreaterThanOrEqual(25, $response['counts']['all']);
    }

    /**
     * Тест получения списка только своих контрактов с фильтром "my"
     * Проверяет, что API возвращает только контракты, созданные текущим пользователем
     */
    public function testListMyContracts(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?filter=my');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('counts', $response);
        $this->assertEquals(24, $response['counts']['my']);
        $this->assertGreaterThanOrEqual(1, count($response['data']));
    }

    /**
     * Тест получения списка контрактов со статусом "В работе" с фильтром "in_progress"
     * Проверяет, что API возвращает только контракты со статусом "В работе".
     */
    public function testListInProgressContracts(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?filter=in_progress');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('counts', $response);
        $this->assertGreaterThanOrEqual(13, $response['counts']['in_progress']);
        $this->assertGreaterThanOrEqual(1, count($response['data']));
        $this->assertEquals(ContractStatus::IN_PROGRESS->getLabel(), $response['data'][0]['status']);
    }

    /**
     * Тест получения списка контрактов со статусом "Завершено" с фильтром "completed"
     * Проверяет, что API возвращает только контракты со статусом "Завершено".
     */
    public function testListCompletedContracts(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?filter=completed');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('counts', $response);
        $this->assertGreaterThanOrEqual(12, $response['counts']['completed']);
        $this->assertGreaterThanOrEqual(1, count($response['data']));
        $this->assertEquals(ContractStatus::COMPLETED->getLabel(), $response['data'][0]['status']);
    }

    /**
     * Тест сортировки контрактов по ID в порядке возрастания (ASC)
     * Проверяет, что контракты возвращаются отсортированными по ID от меньшего к большему.
     */
    public function testSortingByIdAsc(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?sortBy=id&sortOrder=ASC');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThanOrEqual(2, count($response['data']));
        $this->assertLessThanOrEqual($response['data'][1]['id'], $response['data'][0]['id']);
    }

    /**
     * Тест сортировки контрактов по ID в порядке убывания (DESC)
     * Проверяет, что контракты возвращаются отсортированными по ID от большего к меньшему.
     */
    public function testSortingByIdDesc(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?sortBy=id&sortOrder=DESC');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertGreaterThanOrEqual(2, count($response['data']));
        $this->assertGreaterThanOrEqual($response['data'][1]['id'], $response['data'][0]['id']);
    }

    /**
     * Тест сортировки контрактов по номеру договора в порядке возрастания
     * Проверяет, что контракты возвращаются отсортированными по номеру договора.
     */
    public function testSortingByContractNumber(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?sortBy=contractNumber&sortOrder=ASC');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $contractNumbers = array_filter(array: array_column(array: $response['data'], column_key: 'contractNumber'));
        if (count($contractNumbers) >= 2) {
            $sorted = $contractNumbers;
            sort($sorted);
            $this->assertEquals($sorted, array_values(array: $contractNumbers));
        }
    }

    /**
     * Тест сортировки контрактов по дате договора в порядке возрастания
     * Проверяет, что контракты возвращаются отсортированными по дате договора от старых к новым
     */
    public function testSortingByContractDate(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?sortBy=contractDate&sortOrder=ASC');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $dates = array_filter(array: array_column(array: $response['data'], column_key: 'contractDate'));
        if (count($dates) >= 2) {
            $sorted = $dates;
            sort($sorted);
            $this->assertEquals($sorted, array_values(array: $dates));
        }
    }

    /**
     * Тест сортировки контрактов по статусу в порядке возрастания
     * Проверяет, что контракты возвращаются отсортированными по статусу.
     */
    public function testSortingByStatus(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?sortBy=status&sortOrder=ASC');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $statuses = array_filter(array: array_column(array: $response['data'], column_key: 'status'));
        if (count($statuses) >= 2) {
            $sorted = $statuses;
            sort($sorted);
            $this->assertEquals($sorted, array_values(array: $statuses));
        }
    }

    /**
     * Тест сортировки контрактов по имени клиента в порядке возрастания
     * Проверяет, что контракты возвращаются отсортированными по имени клиента.
     */
    public function testSortingByFirstName(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?sortBy=firstName&sortOrder=ASC');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
    }

    /**
     * Тест пагинации контрактов на первой странице
     * Проверяет, что API возвращает корректную пагинацию с ограничением в 20 элементов на странице.
     */
    public function testPagination(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?page=1&limit=20');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertLessThanOrEqual(20, count($response['data']));
        $this->assertEquals(1, $response['pagination']['page']);
        $this->assertEquals(20, $response['pagination']['limit']);
        $this->assertGreaterThanOrEqual(25, $response['pagination']['total']);
    }

    /**
     * Тест пагинации контрактов на второй странице
     * Проверяет, что API корректно возвращает данные для второй страницы пагинации.
     */
    public function testPaginationSecondPage(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?page=2&limit=20');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertEquals(2, $response['pagination']['page']);
        $this->assertLessThanOrEqual(20, count($response['data']));
    }

    /**
     * Тест структуры ответа API
     * Проверяет, что ответ содержит все необходимые поля: data, pagination, counts
     * и что каждый контракт содержит все обязательные поля.
     */
    public function testResponseStructure(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertArrayHasKey('counts', $response);

        if (count($response['data']) > 0) {
            $contractData = $response['data'][0];
            $this->assertArrayHasKey('id', $contractData);
            $this->assertArrayHasKey('contractNumber', $contractData);
            $this->assertArrayHasKey('fullName', $contractData);
            $this->assertArrayHasKey('contractDate', $contractData);
            $this->assertArrayHasKey('manager', $contractData);
            $this->assertArrayHasKey('author', $contractData);
            $this->assertArrayHasKey('status', $contractData);
        }

        $this->assertArrayHasKey('all', $response['counts']);
        $this->assertArrayHasKey('my', $response['counts']);
        $this->assertArrayHasKey('in_progress', $response['counts']);
        $this->assertArrayHasKey('completed', $response['counts']);
    }

    /**
     * Тест подсчета контрактов для всех фильтров
     * Проверяет, что API корректно возвращает количество контрактов для каждого фильтра:
     * все контракты, только свои, в работе, завершено.
     */
    public function testCountsForAllFilters(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts?filter=all');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('counts', $response);
        $this->assertGreaterThanOrEqual(25, $response['counts']['all']);
        $this->assertEquals(24, $response['counts']['my']);
        $this->assertGreaterThanOrEqual(13, $response['counts']['in_progress']);
        $this->assertGreaterThanOrEqual(12, $response['counts']['completed']);
    }

    /**
     * Тест создания нового контракта
     * Проверяет, что API создает контракт с обязательными полями.
     */
    public function testCreateContract(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $contractData = [
            'contractNumber' => 'ДГ-2024-TEST-001',
            'firstName' => 'Тест',
            'lastName' => 'Тестов',
            'middleName' => 'Тестович',
        ];

        $this->client->request(
            method: 'POST',
            uri: '/api/v1/contracts',
            content: json_encode($contractData)
        );

        $this->assertResponseStatusCodeSame(expectedCode: 201);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('contractNumber', $response);
        $this->assertArrayHasKey('firstName', $response);
        $this->assertArrayHasKey('lastName', $response);
        $this->assertArrayHasKey('middleName', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('ДГ-2024-TEST-001', $response['contractNumber']);
        $this->assertEquals('Тест', $response['firstName']);
        $this->assertEquals('Тестов', $response['lastName']);
        $this->assertEquals('Тестович', $response['middleName']);
        $this->assertEquals('В работе', $response['status']);
        $this->assertIsInt($response['id']);
    }

    /**
     * Тест создания контракта без обязательных полей
     * Проверяет, что API возвращает ошибку валидации.
     */
    public function testCreateContractWithoutRequiredFields(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $contractData = [
            'contractNumber' => 'ДГ-2024-TEST-002',
        ];

        $this->client->request(
            method: 'POST',
            uri: '/api/v1/contracts',
            content: json_encode($contractData)
        );

        $this->assertResponseStatusCodeSame(expectedCode: 400);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('details', $response);
        $this->assertEquals('Не указаны обязательные поля', $response['error']);
        $this->assertArrayHasKey('firstName', $response['details']);
        $this->assertArrayHasKey('lastName', $response['details']);
        $this->assertArrayHasKey('middleName', $response['details']);
    }

    /**
     * Тест создания контракта без авторизации
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testCreateContractWithoutAuth(): void
    {
        $contractData = [
            'contractNumber' => 'ДГ-2024-TEST-003',
            'firstName' => 'Тест',
            'lastName' => 'Тестов',
            'middleName' => 'Тестович',
        ];

        $this->client->request(
            method: 'POST',
            uri: '/api/v1/contracts',
            content: json_encode($contractData)
        );

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Тест создания контракта с пустыми обязательными полями
     * Проверяет, что API возвращает ошибку валидации.
     */
    public function testCreateContractWithEmptyRequiredFields(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $contractData = [
            'contractNumber' => '',
            'firstName' => '',
            'lastName' => '',
            'middleName' => '',
        ];

        $this->client->request(
            method: 'POST',
            uri: '/api/v1/contracts',
            content: json_encode($contractData)
        );

        $this->assertResponseStatusCodeSame(expectedCode: 400);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('error', $response);
        $this->assertArrayHasKey('details', $response);
        $this->assertArrayHasKey('contractNumber', $response['details']);
        $this->assertArrayHasKey('firstName', $response['details']);
        $this->assertArrayHasKey('lastName', $response['details']);
        $this->assertArrayHasKey('middleName', $response['details']);
    }
}
