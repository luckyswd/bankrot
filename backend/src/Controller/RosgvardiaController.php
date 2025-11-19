<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Rosgvardia;
use App\Repository\RosgvardiaRepository;
use App\Service\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/rosgvardia')]
#[IsGranted('ROLE_ADMIN')]
class RosgvardiaController extends AbstractController
{
    public function __construct(
        private readonly RosgvardiaRepository $rosgvardiaRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cacheService,
    ) {
    }

    #[Route('', name: 'api_rosgvardia_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/rosgvardia',
        summary: 'Получить список отделений Росгвардии',
        security: [['Bearer' => []]],
        tags: ['Rosgvardia'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по наименованию, адресу',
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
                                    new OA\Property(property: 'name', type: 'string', example: 'Управление Росгвардии по г. Москве'),
                                    new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
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

        $cacheKey = $this->cacheService->getRosgvardiaListKey($page, $limitParam, $search);

        $data = $this->cacheService->get(
            key: $cacheKey,
            callback: function () use ($limitParam, $page, $search) {
                if ($limitParam === 'all') {
                    $qb = $this->rosgvardiaRepository->createSearchQueryBuilder($search);
                    $items = $qb->getQuery()->getResult();
                    $total = count($items);

                    $result = [];

                    foreach ($items as $rosgvardia) {
                        $result[] = [
                            'id' => $rosgvardia->getId(),
                            'name' => $rosgvardia->getName(),
                            'address' => $rosgvardia->getAddress(),
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

                $result = $this->rosgvardiaRepository->findPaginated(
                    page: $page,
                    limit: $limit,
                    search: $search
                );

                $data = [];

                foreach ($result['items'] as $rosgvardia) {
                    $data[] = [
                        'id' => $rosgvardia->getId(),
                        'name' => $rosgvardia->getName(),
                        'address' => $rosgvardia->getAddress(),
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
            pool: 'rosgvardia'
        );

        return $this->json(data: $data);
    }

    #[Route('/{id}', name: 'api_rosgvardia_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/rosgvardia/{id}',
        summary: 'Получить отделение Росгвардии по ID',
        security: [['Bearer' => []]],
        tags: ['Rosgvardia'],
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
                        new OA\Property(property: 'name', type: 'string', example: 'Управление Росгвардии по г. Москве'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
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
        $rosgvardia = $this->rosgvardiaRepository->find($id);

        if (!$rosgvardia instanceof Rosgvardia) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        return $this->json(data: [
            'id' => $rosgvardia->getId(),
            'name' => $rosgvardia->getName(),
            'address' => $rosgvardia->getAddress(),
        ]);
    }

    #[Route('', name: 'api_rosgvardia_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/rosgvardia',
        summary: 'Создать отделение Росгвардии',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'Управление Росгвардии по г. Москве'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Rosgvardia'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Отделение успешно создано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Управление Росгвардии по г. Москве'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
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

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $rosgvardia = new Rosgvardia();
        $rosgvardia->setName(trim($data['name']));
        $rosgvardia->setAddress(isset($data['address']) ? trim($data['address']) : null);

        $this->entityManager->persist($rosgvardia);
        $this->entityManager->flush();

        $this->cacheService->invalidateRosgvardiaLists();

        return $this->json(
            data: [
                'id' => $rosgvardia->getId(),
                'name' => $rosgvardia->getName(),
                'address' => $rosgvardia->getAddress(),
            ],
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_rosgvardia_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/rosgvardia/{id}',
        summary: 'Обновить отделение Росгвардии',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'Управление Росгвардии по г. Москве'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Rosgvardia'],
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
                        new OA\Property(property: 'name', type: 'string', example: 'Управление Росгвардии по г. Москве'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
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
        $rosgvardia = $this->rosgvardiaRepository->find($id);

        if (!$rosgvardia instanceof Rosgvardia) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $rosgvardia->setName(trim($data['name']));
        $rosgvardia->setAddress(isset($data['address']) ? trim($data['address']) : null);

        $this->entityManager->flush();

        $this->cacheService->invalidateRosgvardia($id);
        $this->cacheService->invalidateRosgvardiaLists();

        return $this->json(data: [
            'id' => $rosgvardia->getId(),
            'name' => $rosgvardia->getName(),
            'address' => $rosgvardia->getAddress(),
        ]);
    }

    #[Route('/{id}', name: 'api_rosgvardia_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/rosgvardia/{id}',
        summary: 'Удалить отделение Росгвардии',
        security: [['Bearer' => []]],
        tags: ['Rosgvardia'],
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
        $rosgvardia = $this->rosgvardiaRepository->find($id);

        if (!$rosgvardia instanceof Rosgvardia) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $this->entityManager->remove($rosgvardia);
        $this->entityManager->flush();

        $this->cacheService->invalidateRosgvardia($id);
        $this->cacheService->invalidateRosgvardiaLists();

        return $this->json(data: [], status: 204);
    }
}
