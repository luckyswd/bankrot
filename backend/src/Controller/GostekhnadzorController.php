<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Gostekhnadzor;
use App\Repository\GostekhnadzorRepository;
use App\Service\CacheService;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/gostekhnadzor')]
#[IsGranted('ROLE_ADMIN')]
class GostekhnadzorController extends AbstractController
{
    public function __construct(
        private readonly GostekhnadzorRepository $gostekhnadzorRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cacheService,
    ) {
    }

    #[Route('', name: 'api_gostekhnadzor_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/gostekhnadzor',
        summary: 'Получить список отделений Гостехнадзора',
        security: [['Bearer' => []]],
        tags: ['Gostekhnadzor'],
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
                                    new OA\Property(property: 'name', type: 'string', example: 'Гостехнадзор по г. Москве'),
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

        $cacheKey = $this->cacheService->getGostekhnadzorListKey($page, $limitParam, $search);

        $data = $this->cacheService->get(
            key: $cacheKey,
            callback: function () use ($limitParam, $page, $search) {
                if ($limitParam === 'all') {
                    $qb = $this->gostekhnadzorRepository->createSearchQueryBuilder($search);
                    $items = $qb->getQuery()->getResult();
                    $total = count($items);

                    $result = [];

                    foreach ($items as $gostekhnadzor) {
                        $result[] = [
                            'id' => $gostekhnadzor->getId(),
                            'name' => $gostekhnadzor->getName(),
                            'address' => $gostekhnadzor->getAddress(),
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

                $result = $this->gostekhnadzorRepository->findPaginated(
                    page: $page,
                    limit: $limit,
                    search: $search
                );

                $data = [];

                foreach ($result['items'] as $gostekhnadzor) {
                    $data[] = [
                        'id' => $gostekhnadzor->getId(),
                        'name' => $gostekhnadzor->getName(),
                        'address' => $gostekhnadzor->getAddress(),
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
            pool: 'gostekhnadzor'
        );

        return $this->json(data: $data);
    }

    #[Route('/{id}', name: 'api_gostekhnadzor_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/gostekhnadzor/{id}',
        summary: 'Получить отделение по ID',
        security: [['Bearer' => []]],
        tags: ['Gostekhnadzor'],
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
                        new OA\Property(property: 'name', type: 'string', example: 'Гостехнадзор по г. Москве'),
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
        $gostekhnadzor = $this->gostekhnadzorRepository->find($id);

        if (!$gostekhnadzor instanceof Gostekhnadzor) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        return $this->json(data: [
            'id' => $gostekhnadzor->getId(),
            'name' => $gostekhnadzor->getName(),
            'address' => $gostekhnadzor->getAddress(),
        ]);
    }

    #[Route('', name: 'api_gostekhnadzor_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/gostekhnadzor',
        summary: 'Создать отделение',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'Гостехнадзор по г. Москве'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Gostekhnadzor'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Отделение успешно создано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Гостехнадзор по г. Москве'),
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

        $gostekhnadzor = new Gostekhnadzor();
        $gostekhnadzor->setName(trim($data['name']));
        $gostekhnadzor->setAddress(isset($data['address']) ? trim($data['address']) : null);

        $this->entityManager->persist($gostekhnadzor);
        $this->entityManager->flush();

        $this->cacheService->invalidateGostekhnadzorLists();

        return $this->json(
            data: [
                'id' => $gostekhnadzor->getId(),
                'name' => $gostekhnadzor->getName(),
                'address' => $gostekhnadzor->getAddress(),
            ],
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_gostekhnadzor_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/gostekhnadzor/{id}',
        summary: 'Обновить отделение',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'Гостехнадзор по г. Москве'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Gostekhnadzor'],
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
                        new OA\Property(property: 'name', type: 'string', example: 'Гостехнадзор по г. Москве'),
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
        $gostekhnadzor = $this->gostekhnadzorRepository->find($id);

        if (!$gostekhnadzor instanceof Gostekhnadzor) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $gostekhnadzor->setName(trim($data['name']));
        $gostekhnadzor->setAddress(isset($data['address']) ? trim($data['address']) : null);

        $this->entityManager->flush();

        $this->cacheService->invalidateGostekhnadzor($id);
        $this->cacheService->invalidateGostekhnadzorLists();

        return $this->json(data: [
            'id' => $gostekhnadzor->getId(),
            'name' => $gostekhnadzor->getName(),
            'address' => $gostekhnadzor->getAddress(),
        ]);
    }

    #[Route('/{id}', name: 'api_gostekhnadzor_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/gostekhnadzor/{id}',
        summary: 'Удалить отделение',
        security: [['Bearer' => []]],
        tags: ['Gostekhnadzor'],
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
        $gostekhnadzor = $this->gostekhnadzorRepository->find($id);

        if (!$gostekhnadzor instanceof Gostekhnadzor) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $this->entityManager->remove($gostekhnadzor);
        $this->entityManager->flush();

        $this->cacheService->invalidateGostekhnadzor($id);
        $this->cacheService->invalidateGostekhnadzorLists();

        return $this->json(data: [], status: 204);
    }
}
