<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Mchs;
use App\Repository\MchsRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/mchs')]
#[IsGranted('ROLE_ADMIN')]
class MchsController extends AbstractController
{
    public function __construct(
        private readonly MchsRepository $mchsRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_mchs_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/mchs',
        summary: 'Получить список отделений ГИМС МЧС',
        security: [['Bearer' => []]],
        tags: ['MCHS'],
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
                description: 'Список отделений с пагинацией',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'ГИМС МЧС России по г. Москве'),
                                    new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
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

        $result = $this->mchsRepository->findPaginated(
            page: $page,
            limit: $limit,
            search: $search
        );

        $data = [];

        foreach ($result['items'] as $mchs) {
            $data[] = [
                'id' => $mchs->getId(),
                'name' => $mchs->getName(),
                'address' => $mchs->getAddress(),
                'phone' => $mchs->getPhone(),
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

    #[Route('/{id}', name: 'api_mchs_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/mchs/{id}',
        summary: 'Получить отделение ГИМС МЧС по ID',
        security: [['Bearer' => []]],
        tags: ['MCHS'],
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
                        new OA\Property(property: 'name', type: 'string', example: 'ГИМС МЧС России по г. Москве'),
                        new OA\Property(property: 'address', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                        new OA\Property(property: 'phone', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
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
        $mchs = $this->mchsRepository->find($id);

        if (!$mchs instanceof Mchs) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        return $this->json(data: [
            'id' => $mchs->getId(),
            'name' => $mchs->getName(),
            'address' => $mchs->getAddress(),
            'phone' => $mchs->getPhone(),
        ]);
    }

    #[Route('', name: 'api_mchs_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/mchs',
        summary: 'Создать отделение ГИМС МЧС',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'ГИМС МЧС России по г. Москве'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                    new OA\Property(property: 'phone', description: 'Телефон', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['MCHS'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Отделение успешно создано',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'ГИМС МЧС России по г. Москве'),
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

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $mchs = new Mchs();
        $mchs->setName(trim($data['name']));
        $mchs->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $mchs->setPhone(isset($data['phone']) ? trim($data['phone']) : null);

        $this->entityManager->persist($mchs);
        $this->entityManager->flush();

        return $this->json(
            data: [
                'id' => $mchs->getId(),
                'name' => $mchs->getName(),
                'address' => $mchs->getAddress(),
                'phone' => $mchs->getPhone(),
            ],
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_mchs_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/mchs/{id}',
        summary: 'Обновить отделение ГИМС МЧС',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные отделения',
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', description: 'Наименование', type: 'string', example: 'ГИМС МЧС России по г. Москве'),
                    new OA\Property(property: 'address', description: 'Адрес', type: 'string', example: 'г. Москва, ул. Ленина, д. 1', nullable: true),
                    new OA\Property(property: 'phone', description: 'Телефон', type: 'string', example: '+7 (495) 123-45-67', nullable: true),
                ],
                type: 'object'
            )
        ),
        tags: ['MCHS'],
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
                        new OA\Property(property: 'name', type: 'string', example: 'ГИМС МЧС России по г. Москве'),
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
        $mchs = $this->mchsRepository->find($id);

        if (!$mchs instanceof Mchs) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty(trim($data['name']))) {
            return $this->json(data: ['error' => 'Наименование обязательно'], status: 400);
        }

        $mchs->setName(trim($data['name']));
        $mchs->setAddress(isset($data['address']) ? trim($data['address']) : null);
        $mchs->setPhone(isset($data['phone']) ? trim($data['phone']) : null);

        $this->entityManager->flush();

        return $this->json(data: [
            'id' => $mchs->getId(),
            'name' => $mchs->getName(),
            'address' => $mchs->getAddress(),
            'phone' => $mchs->getPhone(),
        ]);
    }

    #[Route('/{id}', name: 'api_mchs_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/v1/mchs/{id}',
        summary: 'Удалить отделение ГИМС МЧС',
        security: [['Bearer' => []]],
        tags: ['MCHS'],
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
        $mchs = $this->mchsRepository->find($id);

        if (!$mchs instanceof Mchs) {
            return $this->json(data: ['error' => 'Отделение не найдено'], status: 404);
        }

        $this->entityManager->remove($mchs);
        $this->entityManager->flush();

        return $this->json(data: [], status: 204);
    }
}
