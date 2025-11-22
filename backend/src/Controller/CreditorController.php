<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Creditor;
use App\Repository\CreditorRepository;
use App\Service\CacheService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/creditors')]
#[IsGranted('ROLE_ADMIN')]
class CreditorController extends AbstractController
{
    public function __construct(
        private readonly CreditorRepository $creditorRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheService $cacheService,
    ) {
    }

    #[Route('', name: 'api_creditors_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/creditors',
        summary: 'Получить список кредиторов',
        security: [['Bearer' => []]],
        tags: ['Creditors'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по наименованию',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: Types::STRING, example: 'Сбербанк')
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
                schema: new OA\Schema(type: Types::STRING, example: 10, default: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список кредиторов с пагинацией',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: Types::STRING, example: 'ПАО Сбербанк'),
                                    new OA\Property(property: 'address', type: Types::STRING, example: 'г. Москва, ул. Вавилова, д. 19', nullable: true),
                                    new OA\Property(property: 'headFullName', type: Types::STRING, example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                                    new OA\Property(property: 'bankDetails', type: Types::STRING, example: 'БИК: 044525225, ИНН: 7707083893', nullable: true),
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

        $cacheKey = $this->cacheService->getCreditorsListKey($page, $limitParam, $search);

        $data = $this->cacheService->get(
            key: $cacheKey,
            callback: function () use ($limitParam, $page, $search) {
                if ($limitParam === 'all') {
                    $qb = $this->creditorRepository->createSearchQueryBuilder($search);
                    $items = $qb->getQuery()->getResult();
                    $total = count($items);

                    $result = [];

                    foreach ($items as $creditor) {
                        $result[] = [
                            'id' => $creditor->getId(),
                            'name' => $creditor->getName(),
                            'address' => $creditor->getAddress(),
                            'headFullName' => $creditor->getHeadFullName(),
                            'bankDetails' => $creditor->getBankDetails(),
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

                $result = $this->creditorRepository->findPaginated(
                    page: $page,
                    limit: $limit,
                    search: $search
                );

                $data = [];

                foreach ($result['items'] as $creditor) {
                    $data[] = [
                        'id' => $creditor->getId(),
                        'name' => $creditor->getName(),
                        'address' => $creditor->getAddress(),
                        'headFullName' => $creditor->getHeadFullName(),
                        'bankDetails' => $creditor->getBankDetails(),
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
            pool: 'creditors'
        );

        return $this->json(data: $data);
    }

    #[Route('/{id}', name: 'api_creditors_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/creditors/{id}',
        summary: 'Получить кредитора по ID',
        security: [['Bearer' => []]],
        tags: ['Creditors'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID кредитора',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные кредитора',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: Types::STRING, example: 'ПАО Сбербанк'),
                        new OA\Property(property: 'address', type: Types::STRING, example: 'г. Москва, ул. Вавилова, д. 19', nullable: true),
                        new OA\Property(property: 'headFullName', type: Types::STRING, example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                        new OA\Property(property: 'bankDetails', type: Types::STRING, example: 'БИК: 044525225, ИНН: 7707083893', nullable: true),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Кредитор не найден'
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
        $creditor = $this->creditorRepository->find($id);

        if (!$creditor instanceof Creditor) {
            return $this->json(data: ['error' => 'Кредитор не найден'], status: 404);
        }

        return $this->json(data: [
            'id' => $creditor->getId(),
            'name' => $creditor->getName(),
            'address' => $creditor->getAddress(),
            'headFullName' => $creditor->getHeadFullName(),
            'bankDetails' => $creditor->getBankDetails(),
        ]);
    }

    #[Route('', name: 'api_creditors_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/creditors',
        summary: 'Создать кредитора',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные кредитора',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: Types::STRING, example: 'ПАО Сбербанк'),
                    new OA\Property(property: 'address', description: 'Адрес', type: Types::STRING, example: 'г. Москва, ул. Вавилова, д. 19', nullable: true),
                    new OA\Property(property: 'headFullName', description: 'ФИО руководителя', type: Types::STRING, example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                    new OA\Property(property: 'bankDetails', description: 'Банковские реквизиты', type: Types::STRING, example: 'БИК: 044525225, ИНН: 7707083893', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Creditors'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Кредитор успешно создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: Types::STRING, example: 'ПАО Сбербанк'),
                        new OA\Property(property: 'address', type: Types::STRING, example: 'г. Москва, ул. Вавилова, д. 19', nullable: true),
                        new OA\Property(property: 'headFullName', type: Types::STRING, example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                        new OA\Property(property: 'bankDetails', type: Types::STRING, example: 'БИК: 044525225, ИНН: 7707083893', nullable: true),
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

        $creditor = new Creditor();
        $creditor->setName(trim($data['name']));
        $creditor->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $creditor->setHeadFullName(isset($data['headFullName']) ? trim($data['headFullName']) : null);
        $creditor->setBankDetails(isset($data['bankDetails']) ? trim($data['bankDetails']) : null);

        $this->entityManager->persist($creditor);
        $this->entityManager->flush();

        $this->cacheService->invalidateCreditorsLists();

        return $this->json(
            data: [
                'id' => $creditor->getId(),
                'name' => $creditor->getName(),
                'address' => $creditor->getAddress(),
                'headFullName' => $creditor->getHeadFullName(),
                'bankDetails' => $creditor->getBankDetails(),
            ],
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_creditors_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/creditors/{id}',
        summary: 'Обновить кредитора',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные кредитора',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: Types::STRING, example: 'ПАО Сбербанк'),
                    new OA\Property(property: 'address', description: 'Адрес', type: Types::STRING, example: 'г. Москва, ул. Вавилова, д. 19', nullable: true),
                    new OA\Property(property: 'headFullName', description: 'ФИО руководителя', type: Types::STRING, example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                    new OA\Property(property: 'bankDetails', description: 'Банковские реквизиты', type: Types::STRING, example: 'БИК: 044525225, ИНН: 7707083893', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Creditors'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID кредитора',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Кредитор успешно обновлен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: Types::STRING, example: 'ПАО Сбербанк'),
                        new OA\Property(property: 'address', type: Types::STRING, example: 'г. Москва, ул. Вавилова, д. 19', nullable: true),
                        new OA\Property(property: 'headFullName', type: Types::STRING, example: 'ИВАНОВ ИВАН ИВАНОВИЧ', nullable: true),
                        new OA\Property(property: 'bankDetails', type: Types::STRING, example: 'БИК: 044525225, ИНН: 7707083893', nullable: true),
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
                description: 'Кредитор не найден'
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
        $creditor = $this->creditorRepository->find($id);

        if (!$creditor instanceof Creditor) {
            return $this->json(data: ['error' => 'Кредитор не найден'], status: 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $creditor->setName(trim($data['name']));
        $creditor->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $creditor->setHeadFullName(isset($data['headFullName']) ? trim($data['headFullName']) : null);
        $creditor->setBankDetails(isset($data['bankDetails']) ? trim($data['bankDetails']) : null);

        $this->entityManager->flush();

        $this->cacheService->invalidateCreditor($id);
        $this->cacheService->invalidateCreditorsLists();

        return $this->json(data: [
            'id' => $creditor->getId(),
            'name' => $creditor->getName(),
            'address' => $creditor->getAddress(),
            'headFullName' => $creditor->getHeadFullName(),
            'bankDetails' => $creditor->getBankDetails(),
        ]);
    }

    #[Route('/{id}', name: 'api_creditors_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/creditors/{id}',
        summary: 'Удалить кредитора',
        security: [['Bearer' => []]],
        tags: ['Creditors'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID кредитора',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Кредитор успешно удален'
            ),
            new OA\Response(
                response: 404,
                description: 'Кредитор не найден'
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
        $creditor = $this->creditorRepository->find($id);

        if (!$creditor instanceof Creditor) {
            return $this->json(data: ['error' => 'Кредитор не найден'], status: 404);
        }

        $this->entityManager->remove($creditor);
        $this->entityManager->flush();

        $this->cacheService->invalidateCreditor($id);
        $this->cacheService->invalidateCreditorsLists();

        return $this->json(data: [], status: 204);
    }
}
