<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Court;
use App\Repository\CourtRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/courts')]
#[IsGranted('ROLE_ADMIN')]
class CourtController extends AbstractController
{
    public function __construct(
        private readonly CourtRepository $courtRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_courts_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/courts',
        summary: 'Получить список арбитражных судов',
        security: [['Bearer' => []]],
        tags: ['Courts'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по наименованию, адресу, телефону',
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
                description: 'Количество элементов на странице',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 10, default: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список судов с пагинацией',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Арбитражный суд города Москвы'),
                                    new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Большая Тульская, д. 17', nullable: true),
                                    new OA\Property(property: 'phone', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
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
        $search = $request->query->get('search');
        $page = max(1, (int)($request->query->get('page') ?? 1));
        $limit = max(1, min(100, (int)($request->query->get('limit') ?? 10)));

        $result = $this->courtRepository->findPaginated(
            page: $page,
            limit: $limit,
            search: $search
        );

        $data = [];

        foreach ($result['items'] as $court) {
            $data[] = [
                'id' => $court->getId(),
                'name' => $court->getName(),
                'address' => $court->getAddress(),
                'phone' => $court->getPhone(),
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

    #[Route('/{id}', name: 'api_courts_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/courts/{id}',
        summary: 'Получить суд по ID',
        security: [['Bearer' => []]],
        tags: ['Courts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID суда',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные суда',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Арбитражный суд города Москвы'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Большая Тульская, д. 17', nullable: true),
                        new OA\Property(property: 'phone', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Суд не найден'
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
        $court = $this->courtRepository->find($id);

        if (!$court instanceof Court) {
            return $this->json(data: ['error' => 'Суд не найден'], status: 404);
        }

        return $this->json(data: [
            'id' => $court->getId(),
            'name' => $court->getName(),
            'address' => $court->getAddress(),
            'phone' => $court->getPhone(),
        ]);
    }

    #[Route('', name: 'api_courts_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/courts',
        summary: 'Создать суд',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные суда',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'Арбитражный суд города Москвы'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Большая Тульская, д. 17', nullable: true),
                    new OA\Property(property: 'phone', description: 'Телефон', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Courts'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Суд успешно создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Арбитражный суд города Москвы'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Большая Тульская, д. 17', nullable: true),
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

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $court = new Court();
        $court->setName(trim($data['name']));
        $court->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $court->setPhone(isset($data['phone']) ? trim($data['phone']) : null);

        $this->entityManager->persist($court);
        $this->entityManager->flush();

        return $this->json(
            data: [
                'id' => $court->getId(),
                'name' => $court->getName(),
                'address' => $court->getAddress(),
                'phone' => $court->getPhone(),
            ],
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_courts_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/courts/{id}',
        summary: 'Обновить суд',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные суда',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'Арбитражный суд города Москвы'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Большая Тульская, д. 17', nullable: true),
                    new OA\Property(property: 'phone', description: 'Телефон', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['Courts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID суда',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Суд успешно обновлен',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Арбитражный суд города Москвы'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Большая Тульская, д. 17', nullable: true),
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
                response: 404,
                description: 'Суд не найден'
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
        $court = $this->courtRepository->find($id);

        if (!$court instanceof Court) {
            return $this->json(data: ['error' => 'Суд не найден'], status: 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $court->setName(trim($data['name']));
        $court->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $court->setPhone(isset($data['phone']) ? trim($data['phone']) : null);

        $this->entityManager->flush();

        return $this->json(data: [
            'id' => $court->getId(),
            'name' => $court->getName(),
            'address' => $court->getAddress(),
            'phone' => $court->getPhone(),
        ]);
    }

    #[Route('/{id}', name: 'api_courts_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/courts/{id}',
        summary: 'Удалить суд',
        security: [['Bearer' => []]],
        tags: ['Courts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID суда',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Суд успешно удален'
            ),
            new OA\Response(
                response: 404,
                description: 'Суд не найден'
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
        $court = $this->courtRepository->find($id);

        if (!$court instanceof Court) {
            return $this->json(data: ['error' => 'Суд не найден'], status: 404);
        }

        $this->entityManager->remove($court);
        $this->entityManager->flush();

        return $this->json(data: [], status: 204);
    }
}

