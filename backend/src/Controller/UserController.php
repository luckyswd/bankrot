<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/users')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('', name: 'api_users_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/users',
        summary: 'Получить список пользователей',
        security: [['Bearer' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по ФИО или username',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: Types::STRING, example: 'Иванов')
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
                description: 'Количество элементов на странице (или "all" для получения всех записей)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: Types::STRING, default: 10, example: 10)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список пользователей с пагинацией',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: Types::STRING, example: 'Иванов Иван Иванович'),
                                    new OA\Property(property: 'username', type: Types::STRING, example: 'ivanov'),
                                    new OA\Property(property: 'fio', type: Types::STRING, example: 'Иванов Иван Иванович', nullable: true),
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
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $search = $request->query->get('search') ?? '';
        $limitParam = $request->query->get('limit') ?? '10';
        $page = max(1, (int)($request->query->get('page') ?? 1));

        $qb = $this->userRepository->createQueryBuilder('u');

        // Исключаем пользователей с ролью ROLE_ADMIN
        // Роли хранятся как JSON массив, поэтому используем JSON_CONTAINS или LIKE
        $qb->where('u.roles NOT LIKE :adminRole')
            ->setParameter('adminRole', '%ROLE_ADMIN%');

        if (!empty($search)) {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('u.fio', ':search'),
                    $qb->expr()->like('u.username', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('u.fio', 'ASC');

        if ($limitParam === 'all') {
            $items = $qb->getQuery()->getResult();
            $total = count($items);

            $result = [];

            foreach ($items as $user) {
                $fio = $user->getFio() ?? $user->getUsername();
                $result[] = [
                    'id' => $user->getId(),
                    'name' => $fio,
                    'username' => $user->getUsername(),
                    'fio' => $user->getFio(),
                ];
            }

            return $this->json([
                'items' => $result,
                'total' => $total,
                'page' => 1,
                'limit' => $total,
                'pages' => 1,
            ]);
        }

        $limit = max(1, min(1000, (int)$limitParam));
        $offset = ($page - 1) * $limit;

        $totalQb = clone $qb;
        $total = (int)$totalQb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

        $items = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $result = [];

        foreach ($items as $user) {
            $fio = $user->getFio() ?? $user->getUsername();
            $result[] = [
                'id' => $user->getId(),
                'name' => $fio,
                'username' => $user->getUsername(),
                'fio' => $user->getFio(),
            ];
        }

        $pages = (int)ceil($total / $limit);

        return $this->json([
            'items' => $result,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $pages,
        ]);
    }
}
