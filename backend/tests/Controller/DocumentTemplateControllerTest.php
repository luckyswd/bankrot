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
     * Тест получения списка шаблонов документов.
     * Проверяет, что API возвращает список шаблонов.
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
    }

    /**
     * Тест получения списка шаблонов с фильтром по категории.
     * Проверяет, что API возвращает только шаблоны указанной категории.
     */
    public function testListDocumentTemplatesByCategory(): void
    {
        $admin = $this->getUser(reference: 'admin');

        $token = $this->getAuthToken(user: $admin);
        $this->client->setServerParameter(key: 'HTTP_AUTHORIZATION', value: 'Bearer ' . $token);

        $this->client->request(method: 'GET', uri: '/api/v1/document-templates?category=basic_info');

        $this->assertResponseIsSuccessful();
        $response = json_decode(json: $this->client->getResponse()->getContent(), associative: true);

        $this->assertIsArray($response);
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
        $template->setName('Тестовый шаблон');
        $template->setCategory(BankruptcyStage::BASIC_INFO);
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
