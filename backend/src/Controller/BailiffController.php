<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Bailiff;
use App\Repository\BailiffRepository;
use App\Service\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/bailiffs')]
#[IsGranted('ROLE_ADMIN')]
class BailiffController extends AbstractController
{
    public function __construct(
        private readonly BailiffRepository $bailiffRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cacheService,
    ) {
    }

    #[Route('', name: 'api_bailiffs_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/bailiffs',
        summary: 'Получить список отделений судебных приставов',
        security: [['Bearer' => []]],
        tags: ['Bailiffs'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по отделению, адресу',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'Московский')
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Номер страницы (начиная с 1)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1, default: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Количество элементов на странице (или "all" для получения всех записей)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: 10, default: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список отделений с пагинацией',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'department', type: 'string', example: 'Отделение судебных приставов по Московскому району'),
                                    new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                                    new OA\Property(property: 'headFullName', type: 'string', example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
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
        $search = $request->query->get('search') ?? '';
        $limitParam = $request->query->get('limit') ?? '10';
        $page = max(1, (int)($request->query->get('page') ?? 1));

        $cacheKey = $this->cacheService->getBailiffsListKey($page, $limitParam, $search);

        $data = $this->cacheService->get(
            key: $cacheKey,
            callback: function () use ($limitParam, $page, $search) {
                if ($limitParam === 'all') {
                    $qb = $this->bailiffRepository->createSearchQueryBuilder($search);
                    $items = $qb->getQuery()->getResult();
                    $total = count($items);

                    $result = [];

                    foreach ($items as $bailiff) {
                        $result[] = [
                            'id' => $bailiff->getId(),
                            'department' => $bailiff->getDepartment(),
                            'address' => $bailiff->getAddress(),
                            'headFullName' => $bailiff->getHeadFullName(),
                        ];
                    }

                    return [
                        'items' => $result,
                        'total' => $total,
                        'page' => 1,
                        'limit' => 'all',
                        'pages' => 1,
                    ];
                }

                $limit = max(1, min(100, (int)$limitParam));

                $result = $this->bailiffRepository->findPaginated(
                    page: $page,
                    limit: $limit,
                    search: $search
                );

                $data = [];

                foreach ($result['items'] as $bailiff) {
                    $data[] = [
                        'id' => $bailiff->getId(),
                        'department' => $bailiff->getDepartment(),
                        'address' => $bailiff->getAddress(),
                        'headFullName' => $bailiff->getHeadFullName(),
                    ];
                }

                return [
                    'items' => $data,
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'pages' => $result['pages'],
                ];
            },
            ttl: CacheService::TTL_ONE_HOUR,
            pool: 'bailiffs'
        );

        return $this->json(data: $data);
    }

    #[Route('/{id}', name: 'api_bailiffs_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/bailiffs/{id}',
        summary: 'Получить отделение приставов по ID',
        security: [['Bearer' => []]],
        tags: ['Bailiffs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID отделения',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные отделения',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'department', type: 'string', example: 'Отделение судебных приставов по Московскому району'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                        new OA\Property(property: 'headFullName', type: 'string', example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Отделение не найдено'
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
    public function show(int $id): JsonResponse
    {
        $bailiff = $this->bailiffRepository->find($id);

        if (!$bailiff instanceof Bailiff) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        return $this->json(data: [
            'id' => $bailiff->getId(),
            'department' => $bailiff->getDepartment(),
            'address' => $bailiff->getAddress(),
            'headFullName' => $bailiff->getHeadFullName(),
        ]);
    }

    #[Route('', name: 'api_bailiffs_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/bailiffs',
        summary: 'Создать отделение приставов',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['department'],
                properties: [
                    new OA\Property(property: 'department', description: 'Отделение', type: 'string', example: 'Отделение судебных приставов по Московскому району'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                    new OA\Property(property: 'phone', description: 'Телефон', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Bailiffs'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Отделение успешно создано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'department', type: 'string', example: 'Отделение судебных приставов по Московскому району'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                        new OA\Property(property: 'phone', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
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
        $data = json_decode($request->getContent(), true);

        if (!isset($data['department']) || empty(trim($data['department']))) {
            return $this->json(data: ['error' => 'Отделение обязательно'], status: 400);
        }

        $bailiff = new Bailiff();
        $bailiff->setDepartment(trim($data['department']));
        $bailiff->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $bailiff->setHeadFullName(isset($data['headFullName']) ? trim($data['headFullName']) : null);

        $this->entityManager->persist($bailiff);
        $this->entityManager->flush();

        $this->cacheService->invalidateBailiffsLists();

        return $this->json(
            data: [
                'id' => $bailiff->getId(),
                'department' => $bailiff->getDepartment(),
                'address' => $bailiff->getAddress(),
                'headFullName' => $bailiff->getHeadFullName(),
            ],
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_bailiffs_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/bailiffs/{id}',
        summary: 'Обновить отделение приставов',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['department'],
                properties: [
                    new OA\Property(property: 'department', description: 'Отделение', type: 'string', example: 'Отделение судебных приставов по Московскому району'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                    new OA\Property(property: 'headFullName', description: 'Старший пристав', type: 'string', example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Bailiffs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID отделения',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Отделение успешно обновлено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'department', type: 'string', example: 'Отделение судебных приставов по Московскому району'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                        new OA\Property(property: 'headFullName', type: 'string', example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
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
                description: 'Отделение не найдено'
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
        $bailiff = $this->bailiffRepository->find($id);

        if (!$bailiff instanceof Bailiff) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['department']) || empty(trim($data['department']))) {
            return $this->json(data: ['error' => 'Отделение обязательно'], status: 400);
        }

        $bailiff->setDepartment(trim($data['department']));
        $bailiff->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $bailiff->setHeadFullName(isset($data['headFullName']) ? trim($data['headFullName']) : null);

        $this->entityManager->flush();

        $this->cacheService->invalidateBailiff($id);
        $this->cacheService->invalidateBailiffsLists();

        return $this->json(data: [
            'id' => $bailiff->getId(),
            'department' => $bailiff->getDepartment(),
            'address' => $bailiff->getAddress(),
            'headFullName' => $bailiff->getHeadFullName(),
        ]);
    }

    #[Route('/{id}', name: 'api_bailiffs_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/bailiffs/{id}',
        summary: 'Удалить отделение приставов',
        security: [['Bearer' => []]],
        tags: ['Bailiffs'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID отделения',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Отделение успешно удалено'
            ),
            new OA\Response(
                response: 404,
                description: 'Отделение не найдено'
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
        $bailiff = $this->bailiffRepository->find($id);

        if (!$bailiff instanceof Bailiff) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $this->entityManager->remove($bailiff);
        $this->entityManager->flush();

        $this->cacheService->invalidateBailiff($id);
        $this->cacheService->invalidateBailiffsLists();

        return $this->json(data: [], status: 204);
    }
}
