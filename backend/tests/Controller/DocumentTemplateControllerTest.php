<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\Test\TestUserFixtures;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Repository\DocumentTemplateRepository;
use App\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentTemplateControllerTest extends BaseTestCase
{
    /** @var array<string> */
    private array $testFilePaths = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->addFixtures(classes: [TestUserFixtures::class]);
        $this->executeFixtures();
        $this->testFilePaths = [];
    }

    /**
     * Тест получения списка шаблонов документов с пагинацией.
     * Проверяет, что API возвращает список шаблонов с метаданными пагинации.
     */
    public function testListDocumentTemplates(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('page', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('pages', $response);
        $this->assertIsArray($response['items']);
        $this->assertIsInt($response['total']);
        $this->assertIsInt($response['page']);
        $this->assertIsInt($response['limit']);
        $this->assertIsInt($response['pages']);
    }

    /**
     * Тест получения списка шаблонов с фильтром по категории.
     * Проверяет, что API возвращает только шаблоны указанной категории.
     */
    public function testListDocumentTemplatesByCategory(): void
    {
        $admin = $this->getUser(reference: 'admin');

        // Создаём шаблоны разных категорий
        $template1 = $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Шаблон 1');
        $template2 = $this->createTestTemplateWithCategory(BankruptcyStage::PRE_COURT, 'Шаблон 2');
        $template3 = $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Шаблон 3');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates?category=basic_info');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('items', $response);
        $this->assertEquals(2, $response['total']);

        foreach ($response['items'] as $item) {
            $this->assertEquals('Основная информация', $item['category']);
        }
    }

    /**
     * Тест поиска шаблонов по названию.
     * Проверяет, что API возвращает только шаблоны, содержащие поисковый запрос в названии.
     */
    public function testSearchDocumentTemplatesByName(): void
    {
        $admin = $this->getUser(reference: 'admin');

        // Создаём шаблоны с разными названиями
        $template1 = $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Договор купли-продажи');
        $template2 = $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Договор аренды');
        $template3 = $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Акт приёмки');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates?search=Договор');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('items', $response);
        $this->assertEquals(2, $response['total']);

        foreach ($response['items'] as $item) {
            $this->assertStringContainsString('Договор', $item['name']);
        }
    }

    /**
     * Тест пагинации шаблонов.
     * Проверяет, что API возвращает правильные страницы и метаданные.
     */
    public function testPaginationDocumentTemplates(): void
    {
        $admin = $this->getUser(reference: 'admin');

        // Создаём 15 шаблонов
        for ($i = 1; $i <= 15; ++$i) {
            $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, "Шаблон пагинации {$i}");
        }

        // Убеждаемся, что все данные сохранены
        self::$em->flush();

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        // Первая страница (10 элементов)
        $this->client->request(method: 'GET', uri: '/api/v1/document-templates?page=1&limit=10');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('total', $response);
        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('page', $response);
        $this->assertArrayHasKey('limit', $response);
        $this->assertArrayHasKey('pages', $response);

        // Проверяем структуру ответа
        $this->assertEquals(1, $response['page']);
        $this->assertEquals(10, $response['limit']);
        $this->assertIsInt($response['total']);
        $this->assertIsInt($response['pages']);
        $this->assertIsArray($response['items']);

        // Проверяем, что пагинация работает
        $this->assertGreaterThanOrEqual(1, $response['pages']);
        $this->assertLessThanOrEqual($response['limit'], count($response['items']));

        // Проверяем, что total соответствует количеству страниц
        $expectedPages = (int)ceil($response['total'] / $response['limit']);
        $this->assertEquals($expectedPages, $response['pages']);
    }

    /**
     * Тест комбинированного поиска и фильтрации.
     * Проверяет, что API правильно комбинирует поиск по названию и фильтр по категории.
     */
    public function testSearchAndFilterDocumentTemplates(): void
    {
        $admin = $this->getUser(reference: 'admin');

        // Создаём шаблоны
        $template1 = $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Договор купли');
        $template2 = $this->createTestTemplateWithCategory(BankruptcyStage::PRE_COURT, 'Договор аренды');
        $template3 = $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Акт приёмки');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates?search=Договор&category=basic_info');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('items', $response);
        $this->assertEquals(1, $response['total']);

        $this->assertEquals('Договор купли', $response['items'][0]['name']);
        $this->assertEquals('Основная информация', $response['items'][0]['category']);
    }

    /**
     * Тест получения списка шаблонов без авторизации.
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testListDocumentTemplatesWithoutAuth(): void
    {
        $this->client->request(method: 'GET', uri: '/api/v1/document-templates');

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Тест загрузки шаблона документа.
     * Проверяет, что API успешно загружает шаблон.
     */
    public function testCreateDocumentTemplate(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $file = $this->createTestDocxFile();

        $this->client->request(
            method: 'POST',
            uri: '/api/v1/document-templates',
            parameters: [
                'name' => 'Тестовый шаблон',
                'category' => 'basic_info',
            ],
            files: [
                'file' => $file,
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 201);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('category', $response);
        $this->assertEquals('Тестовый шаблон', $response['name']);
        $this->assertEquals('Основная информация', $response['category']);

        // Проверяем, что файл сохранен
        /** @var DocumentTemplateRepository $repository */
        $repository = self::$em->getRepository(DocumentTemplate::class);
        $template = $repository->find($response['id']);

        $this->assertNotNull($template);
        $this->assertFileExists($template->getPath());
        $this->testFilePaths[] = $template->getPath();
    }

    /**
     * Тест загрузки шаблона без файла.
     * Проверяет, что API возвращает ошибку 400.
     */
    public function testCreateDocumentTemplateWithoutFile(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(
            method: 'POST',
            uri: '/api/v1/document-templates',
            parameters: [
                'name' => 'Тестовый шаблон',
                'category' => 'basic_info',
            ],
            server: [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 400);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
    }

    /**
     * Тест загрузки шаблона без авторизации.
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testCreateDocumentTemplateWithoutAuth(): void
    {
        $file = $this->createTestDocxFile();

        $this->client->request(
            method: 'POST',
            uri: '/api/v1/document-templates',
            parameters: [
                'name' => 'Тестовый шаблон',
                'category' => 'basic_info',
            ],
            files: [
                'file' => $file,
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Тест скачивания шаблона документа.
     * Проверяет, что API возвращает файл.
     */
    public function testShowDocumentTemplate(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        // Создаем тестовый шаблон
        $template = $this->createTestTemplate();

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates/' . $template->getId());

        $this->assertResponseIsSuccessful();
        $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', $this->client->getResponse()->headers->get('Content-Type'));
    }

    /**
     * Тест скачивания несуществующего шаблона.
     * Проверяет, что API возвращает ошибку 404.
     */
    public function testShowNonExistentDocumentTemplate(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates/99999');

        $this->assertResponseStatusCodeSame(expectedCode: 404);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
    }

    /**
     * Тест скачивания шаблона без авторизации.
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testShowDocumentTemplateWithoutAuth(): void
    {
        $template = $this->createTestTemplate();

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates/' . $template->getId());

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Тест обновления шаблона документа.
     * Проверяет, что API успешно обновляет шаблон.
     */
    public function testUpdateDocumentTemplate(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $template = $this->createTestTemplate();
        $oldPath = $template->getPath();
        // Отслеживаем старый файл для очистки
        $this->testFilePaths[] = $oldPath;

        $file = $this->createTestDocxFile();

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/document-templates/' . $template->getId(),
            files: [
                'file' => $file,
            ]
        );

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals($template->getId(), $response['id']);

        // Проверяем, что старый файл удален, а новый создан
        self::$em->refresh($template);
        $this->assertFileDoesNotExist($oldPath);
        $this->assertFileExists($template->getPath());
        $this->testFilePaths[] = $template->getPath();
    }

    /**
     * Тест обновления несуществующего шаблона.
     * Проверяет, что API возвращает ошибку 404.
     */
    public function testUpdateNonExistentDocumentTemplate(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $file = $this->createTestDocxFile();

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/document-templates/99999',
            files: [
                'file' => $file,
            ],
            server: [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 404);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
    }

    /**
     * Тест обновления шаблона без авторизации.
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testUpdateDocumentTemplateWithoutAuth(): void
    {
        $template = $this->createTestTemplate();
        $file = $this->createTestDocxFile();

        $this->client->request(
            method: 'PUT',
            uri: '/api/v1/document-templates/' . $template->getId(),
            files: [
                'file' => $file,
            ],
            server: [
                'CONTENT_TYPE' => 'multipart/form-data',
            ]
        );

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Тест удаления шаблона документа.
     * Проверяет, что API успешно удаляет шаблон.
     */
    public function testDeleteDocumentTemplate(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $template = $this->createTestTemplate();
        $templateId = $template->getId();
        $filePath = $template->getPath();

        $this->client->request(method: 'DELETE', uri: '/api/v1/document-templates/' . $templateId);

        $this->assertResponseStatusCodeSame(expectedCode: 204);

        // Проверяем, что шаблон удален из БД
        self::$em->clear();
        /** @var DocumentTemplateRepository $repository */
        $repository = self::$em->getRepository(DocumentTemplate::class);
        $deletedTemplate = $repository->find($templateId);

        $this->assertNull($deletedTemplate);
        $this->assertFileDoesNotExist($filePath);
    }

    /**
     * Тест удаления несуществующего шаблона.
     * Проверяет, что API возвращает ошибку 404.
     */
    public function testDeleteNonExistentDocumentTemplate(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'DELETE', uri: '/api/v1/document-templates/99999');

        $this->assertResponseStatusCodeSame(expectedCode: 404);
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
    }

    /**
     * Тест удаления шаблона без авторизации.
     * Проверяет, что API возвращает ошибку 401.
     */
    public function testDeleteDocumentTemplateWithoutAuth(): void
    {
        $template = $this->createTestTemplate();

        $this->client->request(method: 'DELETE', uri: '/api/v1/document-templates/' . $template->getId());

        $this->assertResponseStatusCodeSame(expectedCode: 401);
    }

    /**
     * Создает тестовый DOCX файл.
     */
    private function createTestDocxFile(): UploadedFile
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_') . '.docx';
        file_put_contents($tempFile, 'test docx content');

        return new UploadedFile(
            path: $tempFile,
            originalName: 'test.docx',
            mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            test: true
        );
    }

    /**
     * Создает тестовый шаблон документа в БД.
     */
    private function createTestTemplate(): DocumentTemplate
    {
        return $this->createTestTemplateWithCategory(BankruptcyStage::BASIC_INFO, 'Тестовый шаблон');
    }

    /**
     * Создает тестовый шаблон документа в БД с указанной категорией и названием.
     */
    private function createTestTemplateWithCategory(BankruptcyStage $category, string $name): DocumentTemplate
    {
        $container = $this->client->getContainer();
        $parameterBag = $container->get('parameter_bag');
        $projectDir = $parameterBag->get('kernel.project_dir');
        $uploadDir = $projectDir . '/var/document-templates';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . '/' . uniqid('', true) . '_test.docx';
        file_put_contents($filePath, 'test docx content');
        $this->testFilePaths[] = $filePath;

        $template = new DocumentTemplate();
        $template->setName($name);
        $template->setCategory($category);
        $template->setPath($filePath);

        self::$em->persist($template);
        self::$em->flush();

        return $template;
    }

    protected function tearDown(): void
    {
        // Очистка только тестовых файлов, созданных в этом тесте
        foreach ($this->testFilePaths as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }

        parent::tearDown();
    }
}
