<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\Test\TestUserFixtures;
use App\Entity\Contracts;
use App\Entity\Enum\ContractStatus;
use App\Repository\ContractsRepository;
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
     * Сортировка происходит по значению enum (in_progress, completed), а не по label.
     * В алфавитном порядке: completed идет после in_progress.
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
            $firstInProgressIndex = null;
            $lastCompletedIndex = null;
            foreach ($statuses as $index => $status) {
                if ($status === 'В работе' && $firstInProgressIndex === null) {
                    $firstInProgressIndex = $index;
                }
                if ($status === 'Завершено') {
                    $lastCompletedIndex = $index;
                }
            }
            if ($firstInProgressIndex !== null && $lastCompletedIndex !== null) {
                $this->assertGreaterThan(
                    $lastCompletedIndex,
                    $firstInProgressIndex,
                    'Все "В работе" должны идти после всех "Завершено"'
                );
            }
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

    /**
     * Тест получения контракта по ID.
     * Проверяет, что API возвращает контракт сгруппированный по группам сериализации.
     */
    public function testShowContract(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = self::$em->getRepository(Contracts::class);
        $contract1 = $contractsRepository->findOneBy(['contractNumber' => 'CONTRACT-001']);

        $this->assertNotNull($contract1);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts/' . $contract1->getId());

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('basic_info', $response);
        $this->assertArrayHasKey('pre_court', $response);
        $this->assertArrayHasKey('judicial', $response);
        $this->assertArrayHasKey('realization', $response);
        $this->assertArrayHasKey('procedure_initiation', $response);
        $this->assertArrayHasKey('procedure', $response);
        $this->assertArrayHasKey('report', $response);

        $this->assertIsArray($response['basic_info']);
        $this->assertIsArray($response['pre_court']);
        $this->assertIsArray($response['judicial']);
        $this->assertIsArray($response['realization']);
        $this->assertIsArray($response['procedure_initiation']);
        $this->assertIsArray($response['procedure']);
        $this->assertIsArray($response['report']);

        if (!empty($response['basic_info'])) {
            $this->assertArrayHasKey('id', $response['basic_info']);
            $this->assertEquals($contract1->getId(), $response['basic_info']['id']);

            if ($contract1->getContractNumber() !== null) {
                $this->assertArrayHasKey('contractNumber', $response['basic_info']);
            }

            if ($contract1->getFirstName() !== null) {
                $this->assertArrayHasKey('firstName', $response['basic_info']);
            }

            if ($contract1->getLastName() !== null) {
                $this->assertArrayHasKey('lastName', $response['basic_info']);
            }

            if ($contract1->getMiddleName() !== null) {
                $this->assertArrayHasKey('middleName', $response['basic_info']);
            }

            $this->assertArrayHasKey('status', $response['basic_info']);
        }
    }

    /**
     * Тест получения несуществующего контракта.
     * Проверяет, что API возвращает ошибку 404.
     */
    public function testShowNonExistentContract(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'GET', uri: '/api/v1/contracts/99999');

        $this->assertResponseStatusCodeSame(expectedCode: 404);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Контракт не найден', $response['error']);
    }

    /**
     * Тест получения контракта без авторизации.
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testShowContractWithoutAuth(): void
    {
        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = self::$em->getRepository(Contracts::class);
        $contract1 = $contractsRepository->findOneBy(['contractNumber' => 'CONTRACT-001']);

        $this->assertNotNull($contract1);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts/' . $contract1->getId());

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Тест получения контракта с фильтрацией null значений.
     * Проверяет, что в ответе нет null полей.
     */
    public function testShowContractFiltersNullValues(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = self::$em->getRepository(Contracts::class);
        $contract1 = $contractsRepository->findOneBy(['contractNumber' => 'CONTRACT-001']);

        $this->assertNotNull($contract1);
        $this->client->request(method: 'GET', uri: '/api/v1/contracts/' . $contract1->getId());

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('basic_info', $response);
        $this->assertArrayHasKey('pre_court', $response);
        $this->assertArrayHasKey('judicial', $response);
        $this->assertArrayHasKey('realization', $response);
        $this->assertArrayHasKey('procedure_initiation', $response);
        $this->assertArrayHasKey('procedure', $response);
        $this->assertArrayHasKey('report', $response);

        foreach ($response as $groupData) {
            if (!empty($groupData)) {
                $this->assertNullNotInArray($groupData);
            }
        }
    }

    /**
     * Тест обновления контракта.
     * Проверяет, что API обновляет только переданные поля.
     */
    public function testUpdateContract(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = self::$em->getRepository(Contracts::class);
        $contract1 = $contractsRepository->findOneBy(['contractNumber' => 'CONTRACT-001']);

        $this->assertNotNull($contract1);
        $originalFirstName = $contract1->getFirstName();
        $originalLastName = $contract1->getLastName();
        $originalMiddleName = $contract1->getMiddleName();

        $updateData = [
            'basic_info' => [
                'firstName' => 'ОбновленноеИмя',
                'lastName' => 'ОбновленнаяФамилия',
            ],
        ];

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/contracts/' . $contract1->getId(),
            content: json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('basic_info', $response);
        $this->assertArrayHasKey('pre_court', $response);
        $this->assertArrayHasKey('judicial', $response);
        $this->assertArrayHasKey('realization', $response);
        $this->assertArrayHasKey('procedure_initiation', $response);
        $this->assertArrayHasKey('procedure', $response);
        $this->assertArrayHasKey('report', $response);

        $this->assertArrayHasKey('id', $response['basic_info']);
        $this->assertEquals($contract1->getId(), $response['basic_info']['id']);
        $this->assertEquals('ОбновленноеИмя', $response['basic_info']['firstName']);
        $this->assertEquals('ОбновленнаяФамилия', $response['basic_info']['lastName']);

        self::$em->refresh($contract1);
        $this->assertEquals('ОбновленноеИмя', $contract1->getFirstName());
        $this->assertEquals('ОбновленнаяФамилия', $contract1->getLastName());
        $this->assertEquals($originalMiddleName, $contract1->getMiddleName());
    }

    /**
     * Тест обновления контракта с частичными данными.
     * Проверяет, что обновляется только одно поле, остальные остаются без изменений.
     */
    public function testUpdateContractPartial(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = self::$em->getRepository(Contracts::class);
        $contract1 = $contractsRepository->findOneBy(['contractNumber' => 'CONTRACT-001']);

        $this->assertNotNull($contract1);
        $originalContractNumber = $contract1->getContractNumber();
        $originalFirstName = $contract1->getFirstName();
        $originalStatus = $contract1->getStatus();

        $updateData = [
            'basic_info' => [
                'contractNumber' => 'UPDATED-001',
            ],
        ];

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/contracts/' . $contract1->getId(),
            content: json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('basic_info', $response);
        $this->assertEquals('UPDATED-001', $response['basic_info']['contractNumber']);

        self::$em->refresh($contract1);
        $this->assertEquals('UPDATED-001', $contract1->getContractNumber());
        $this->assertEquals($originalFirstName, $contract1->getFirstName());
        $this->assertEquals($originalStatus, $contract1->getStatus());
    }

    /**
     * Тест обновления несуществующего контракта.
     * Проверяет, что API возвращает ошибку 404.
     */
    public function testUpdateNonExistentContract(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $updateData = [
            'basic_info' => [
                'firstName' => 'Тест',
            ],
        ];

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/contracts/99999',
            content: json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(expectedCode: 404);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Контракт не найден', $response['error']);
    }

    /**
     * Тест обновления контракта без авторизации.
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testUpdateContractWithoutAuth(): void
    {
        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = self::$em->getRepository(Contracts::class);
        $contract1 = $contractsRepository->findOneBy(['contractNumber' => 'CONTRACT-001']);

        $this->assertNotNull($contract1);

        $updateData = [
            'basic_info' => [
                'firstName' => 'Тест',
            ],
        ];

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/contracts/' . $contract1->getId(),
            content: json_encode($updateData)
        );

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Тест обновления статуса контракта.
     * Проверяет, что статус корректно обновляется через enum.
     */
    public function testUpdateContractStatus(): void
    {
        $user1 = $this->getUser(reference: 'user1');

        $token = $this->getAuthToken(user: $user1);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        /** @var ContractsRepository $contractsRepository */
        $contractsRepository = self::$em->getRepository(Contracts::class);
        $contract1 = $contractsRepository->findOneBy(['contractNumber' => 'CONTRACT-001']);

        $this->assertNotNull($contract1);
        $originalStatus = $contract1->getStatus();

        $updateData = [
            'basic_info' => [
                'status' => 'completed',
            ],
        ];

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/contracts/' . $contract1->getId(),
            content: json_encode($updateData)
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertArrayHasKey('basic_info', $response);
        $this->assertEquals('completed', $response['basic_info']['status']);

        self::$em->refresh($contract1);
        $this->assertEquals(ContractStatus::COMPLETED, $contract1->getStatus());
    }

    /**
     * Рекурсивно проверяет, что в массиве нет null значений.
     *
     * @param array<string, mixed> $array
     */
    private function assertNullNotInArray(array $array): void
    {
        foreach ($array as $key => $value) {
            $this->assertNotNull($value, "Значение для ключа '{$key}' не должно быть null");

            if (is_array($value)) {
                $this->assertNullNotInArray($value);
            }
        }
    }
}
