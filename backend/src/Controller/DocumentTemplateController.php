<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts;
use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use App\Repository\ContractsRepository;
use App\Repository\DocumentTemplateRepository;
use App\Service\DocumentTemplateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/document-templates')]
#[IsGranted('ROLE_ADMIN')]
class DocumentTemplateController extends AbstractController
{
    public function __construct(
        private readonly DocumentTemplateRepository $documentTemplateRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentTemplateProcessor $templateProcessor,
        private readonly ContractsRepository $contractsRepository,
    ) {
    }

    private function getUploadDir(): string
    {
        $projectDir = $this->getParameter('kernel.project_dir');

        return $projectDir . '/var/document-templates';
    }

    private function ensureUploadDirExists(string $uploadDir): void
    {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    }

    #[Route('', name: 'api_document_templates_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/document-templates',
        summary: 'Получить список шаблонов документов',
        security: [['Bearer' => []]],
        tags: ['DocumentTemplates'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по названию шаблона',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'договор')
            ),
            new OA\Parameter(
                name: 'category',
                description: 'Фильтр по категории',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: BankruptcyStage::class, example: 'Досудебка')
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Номер страницы (начиная с 1)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Количество элементов на странице',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 10, example: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список шаблонов с пагинацией',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Шаблон договора'),
                                    new OA\Property(property: 'category', type: 'string', example: 'Досудебка'),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(property: 'total', type: 'integer', example: 25),
                        new OA\Property(property: 'page', type: 'integer', example: 1),
                        new OA\Property(property: 'limit', type: 'integer', example: 10),
                        new OA\Property(property: 'pages', type: 'integer', example: 3),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации параметров'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен (требуется роль ROLE_ADMIN)'
            ),
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $search = $request->query->get('search');
        $categoryValue = $request->query->get('category');
        $page = max(1, (int)($request->query->get('page') ?? 1));
        $limit = max(1, min(100, (int)($request->query->get('limit') ?? 10)));

        $category = null;

        if ($categoryValue !== null && $categoryValue !== '') {
            try {
                $category = BankruptcyStage::from($categoryValue);
            } catch (\ValueError $e) {
                return $this->json(data: ['error' => 'Неверная категория'], status: 400);
            }
        }

        $result = $this->documentTemplateRepository->findPaginated(
            page: $page,
            limit: $limit,
            search: $search,
            category: $category
        );

        $data = [];

        foreach ($result['items'] as $template) {
            $data[] = [
                'id' => $template->getId(),
                'name' => $template->getName(),
                'category' => $template->getCategory()->getLabel(),
            ];
        }

        return $this->json(data: [
            'items' => $data,
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'pages' => $result['pages'],
        ]);
    }

    #[Route('', name: 'api_document_templates_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/document-templates',
        summary: 'Загрузить шаблон документа',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Файл шаблона и метаданные',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file', 'name', 'category'],
                    properties: [
                        new OA\Property(property: 'file', description: 'DOCX файл', type: 'string', format: 'binary'),
                        new OA\Property(property: 'name', description: 'Название файла', type: 'string', example: 'Шаблон договора'),
                        new OA\Property(property: 'category', description: 'Категория шаблона', type: 'string', enum: BankruptcyStage::class, example: 'Досудебка'),
                    ]
                )
            )
        ),
        tags: ['DocumentTemplates'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Шаблон успешно загружен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Шаблон договора'),
                        new OA\Property(property: 'category', type: 'string', example: 'Досудебка'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен (требуется роль ROLE_ADMIN)'
            ),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return $this->json(data: ['error' => 'Файл не загружен'], status: 400);
        }

        if (!str_ends_with($file->getClientOriginalName(), '.docx')) {
            return $this->json(data: ['error' => 'Поддерживаются только DOCX файлы'], status: 400);
        }

        $name = $request->request->get('name');
        $categoryValue = $request->request->get('category');

        if (empty($name)) {
            return $this->json(data: ['error' => 'Название файла обязательно'], status: 400);
        }

        if (empty($categoryValue)) {
            return $this->json(data: ['error' => 'Категория обязательна'], status: 400);
        }

        try {
            $category = BankruptcyStage::from($categoryValue);
        } catch (\ValueError $e) {
            return $this->json(data: ['error' => 'Неверная категория'], status: 400);
        }

        $existingTemplate = $this->documentTemplateRepository->findByNameAndCategory(name: $name, category: $category);

        $uploadDir = $this->getUploadDir();
        $this->ensureUploadDirExists($uploadDir);

        $fileName = uniqid('', true) . '_' . $file->getClientOriginalName();
        $filePath = $uploadDir . '/' . $fileName;

        try {
            $file->move($uploadDir, $fileName);
        } catch (FileException $e) {
            return $this->json(data: ['error' => 'Ошибка при сохранении файла ' . $e->getMessage()], status: 500);
        }

        if ($existingTemplate instanceof DocumentTemplate) {
            $oldFilePath = $existingTemplate->getPath();

            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }

            $existingTemplate->setPath($filePath);
            $template = $existingTemplate;
        } else {
            $template = new DocumentTemplate();
            $template->setName($name);
            $template->setCategory($category);
            $template->setPath($filePath);
            $this->entityManager->persist($template);
        }

        $this->entityManager->flush();

        return $this->json(
            data: [
                'id' => $template->getId(),
                'name' => $template->getName(),
                'category' => $template->getCategory()->getLabel(),
            ],
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_document_templates_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/document-templates/{id}',
        summary: 'Скачать шаблон документа',
        security: [['Bearer' => []]],
        tags: ['DocumentTemplates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID шаблона',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Файл шаблона',
                content: new OA\MediaType(
                    mediaType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Шаблон не найден'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен (требуется роль ROLE_ADMIN)'
            ),
        ]
    )]
    public function show(int $id): BinaryFileResponse|JsonResponse
    {
        $template = $this->documentTemplateRepository->find($id);

        if (!$template instanceof DocumentTemplate) {
            return $this->json(data: ['error' => 'Шаблон не найден'], status: 404);
        }

        $filePath = $template->getPath();

        if (!file_exists($filePath)) {
            return $this->json(data: ['error' => 'Файл не найден на сервере'], status: 404);
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->setContentDisposition(
            disposition: ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            filename: $template->getName() . '.docx'
        );

        return $response;
    }

    #[Route('/{id}', name: 'api_document_templates_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/document-templates/{id}',
        summary: 'Обновить шаблон документа',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Новый файл шаблона',
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', description: 'DOCX файл', type: 'string', format: 'binary'),
                    ]
                )
            )
        ),
        tags: ['DocumentTemplates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID шаблона',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Шаблон успешно обновлен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Шаблон договора'),
                        new OA\Property(property: 'category', type: 'string', example: 'Досудебка'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации'
            ),
            new OA\Response(
                response: 404,
                description: 'Шаблон не найден'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен (требуется роль ROLE_ADMIN)'
            ),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $template = $this->documentTemplateRepository->find($id);

        if (!$template instanceof DocumentTemplate) {
            return $this->json(data: ['error' => 'Шаблон не найден'], status: 404);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return $this->json(data: ['error' => 'Файл не загружен'], status: 400);
        }

        if (!str_ends_with($file->getClientOriginalName(), '.docx')) {
            return $this->json(data: ['error' => 'Поддерживаются только DOCX файлы'], status: 400);
        }

        $oldFilePath = $template->getPath();

        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        $uploadDir = $this->getUploadDir();
        $this->ensureUploadDirExists($uploadDir);
        $fileName = uniqid('', true) . '_' . $file->getClientOriginalName();
        $newFilePath = $uploadDir . '/' . $fileName;

        try {
            $file->move($uploadDir, $fileName);
        } catch (FileException $e) {
            return $this->json(data: ['error' => 'Ошибка при сохранении файла'], status: 500);
        }

        $template->setPath($newFilePath);
        $this->entityManager->flush();

        return $this->json(data: [
            'id' => $template->getId(),
            'name' => $template->getName(),
            'category' => $template->getCategory()->getLabel(),
        ]);
    }

    #[Route('/{id}', name: 'api_document_templates_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/document-templates/{id}',
        summary: 'Удалить шаблон документа',
        security: [['Bearer' => []]],
        tags: ['DocumentTemplates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID шаблона',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Шаблон успешно удален'
            ),
            new OA\Response(
                response: 404,
                description: 'Шаблон не найден'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен (требуется роль ROLE_ADMIN)'
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $template = $this->documentTemplateRepository->find($id);

        if (!$template instanceof DocumentTemplate) {
            return $this->json(data: ['error' => 'Шаблон не найден'], status: 404);
        }

        $filePath = $template->getPath();

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->entityManager->remove($template);
        $this->entityManager->flush();

        return $this->json(data: [], status: 204);
    }

    #[Route('/{id}/generate', name: 'api_document_templates_generate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        path: '/api/v1/document-templates/{id}/generate',
        summary: 'Сгенерировать документ из шаблона',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Параметры для генерации документа',
            required: true,
            content: new OA\JsonContent(
                required: ['contractId'],
                properties: [
                    new OA\Property(property: 'contractId', description: 'ID контракта', type: 'integer', example: 1),
                ],
                type: 'object'
            )
        ),
        tags: ['DocumentTemplates'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID шаблона',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Сгенерированный документ',
                content: new OA\MediaType(
                    mediaType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации'
            ),
            new OA\Response(
                response: 404,
                description: 'Шаблон не найден'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен'
            ),
        ]
    )]
    public function generate(int $id, Request $request): Response|JsonResponse
    {
        $template = $this->documentTemplateRepository->find($id);

        if (!$template instanceof DocumentTemplate) {
            return $this->json(data: ['error' => 'Шаблон не найден'], status: 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (!isset($data['contractId'])) {
            return $this->json(data: ['error' => 'Параметр contractId обязателен'], status: 400);
        }

        $contractId = (int)$data['contractId'];
        $contract = $this->contractsRepository->find($contractId);

        if (!$contract instanceof Contracts) {
            return $this->json(data: ['error' => 'Контракт не найден'], status: 404);
        }

        try {
            $outputPath = $this->templateProcessor->processTemplate(template: $template, contract: $contract);
        } catch (\Exception $e) {
            return $this->json(data: ['error' => 'Ошибка при обработке шаблона: ' . $e->getMessage()], status: 500);
        }

        if (!file_exists($outputPath)) {
            return $this->json(data: ['error' => 'Ошибка при создании файла'], status: 500);
        }

        $fileName = $template->getName() . '.docx';
        $fileName = preg_replace('/[^a-zA-Z0-9а-яА-ЯёЁ\s\-_\.]/u', '', $fileName);
        $fileContent = file_get_contents($outputPath);

        if ($fileContent === false) {
            unlink($outputPath);

            return $this->json(data: ['error' => 'Ошибка при чтении файла'], status: 500);
        }

        $response = new Response($fileContent);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $response->headers->set('Content-Length', (string)strlen($fileContent));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($fileName) . '"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');

        unlink($outputPath);

        return $response;
    }
}
