<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts;
use App\Entity\Enum\ContractStatus;
use App\Entity\User;
use App\Repository\ContractsRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class ContractsController extends AbstractController
{
    public function __construct(
        private readonly ContractsRepository $contractsRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/contracts', name: 'api_contracts_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/contracts',
        summary: 'Получить список контрактов с фильтрацией, сортировкой и пагинацией',
        security: [['Bearer' => []]],
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'filter',
                description: 'Фильтр: all, my, in_progress, completed',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'all', enum: ['all', 'my', 'in_progress', 'completed'])
            ),
            new OA\Parameter(
                name: 'sortBy',
                description: 'Поле для сортировки: id, contractNumber, firstName, lastName, middleName, contractDate, status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'sortOrder',
                description: 'Направление сортировки: ASC или DESC',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'ASC', enum: ['ASC', 'DESC'])
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Номер страницы',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
            new OA\Parameter(
                name: 'limit',
                description: 'Количество элементов на странице',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, maximum: 100, minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список контрактов с метаданными',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            description: 'Список контрактов',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'contractNumber', type: 'string', nullable: true),
                                    new OA\Property(property: 'fullName', type: 'string', nullable: true),
                                    new OA\Property(property: 'contractDate', type: 'string', format: 'date', nullable: true),
                                    new OA\Property(property: 'manager', type: 'object', nullable: true),
                                    new OA\Property(property: 'author', type: 'object', nullable: true),
                                    new OA\Property(property: 'status', type: 'string'),
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            description: 'Метаданные пагинации',
                            properties: [
                                new OA\Property(property: 'total', description: 'Общее количество контрактов', type: 'integer'),
                                new OA\Property(property: 'page', description: 'Текущая страница', type: 'integer'),
                                new OA\Property(property: 'limit', description: 'Количество элементов на странице', type: 'integer'),
                                new OA\Property(property: 'pages', description: 'Общее количество страниц', type: 'integer'),
                            ],
                            type: 'object'
                        ),
                        new OA\Property(
                            property: 'counts',
                            description: 'Количество контрактов по фильтрам',
                            properties: [
                                new OA\Property(property: 'all', type: 'integer'),
                                new OA\Property(property: 'my', type: 'integer'),
                                new OA\Property(property: 'in_progress', type: 'integer'),
                                new OA\Property(property: 'completed', type: 'integer'),
                            ],
                            type: 'object'
                        ),
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
        /** @var User|null $user */
        $user = $this->getUser();

        $filter = $request->query->get('filter', 'all');
        $sortBy = $request->query->get('sortBy');
        $sortOrder = $request->query->get('sortOrder', 'ASC');
        $page = max(1, (int)($request->query->get('page') ?? 1));
        $limit = min(100, max(1, (int)($request->query->get('limit') ?? 20)));

        $contracts = $this->contractsRepository->findWithFilters(
            filter: $filter,
            user: $user,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
            page: $page,
            limit: $limit
        );

        $total = $this->contractsRepository->countByFilter(filter: $filter, user: $user);

        $data = [];

        foreach ($contracts as $contract) {
            $data[] = [
                'id' => $contract->getId(),
                'contractNumber' => $contract->getContractNumber(),
                'fullName' => $contract->getFullName(),
                'contractDate' => $contract->getContractDate()?->format('Y-m-d'),
                'manager' => $contract->getManager()?->getFio() ?? null,
                'author' => $contract->getAuthor()->getFio(),
                'status' => $contract->getStatus()->getLabel(),
            ];
        }

        $counts = [
            'all' => $this->contractsRepository->countByFilter(filter: 'all', user: $user),
            'my' => $this->contractsRepository->countByFilter(filter: 'my', user: $user),
            'in_progress' => $this->contractsRepository->countByFilter(filter: 'in_progress', user: $user),
            'completed' => $this->contractsRepository->countByFilter(filter: 'completed', user: $user),
        ];

        return $this->json(data: [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int)ceil($total / $limit),
            ],
            'counts' => $counts,
        ]);
    }

    #[Route('/contracts', name: 'api_contracts_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/v1/contracts',
        summary: 'Создать новый контракт',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные для создания контракта',
            required: true,
            content: new OA\JsonContent(
                required: ['contractNumber', 'firstName', 'lastName', 'middleName'],
                properties: [
                    new OA\Property(
                        property: 'contractNumber',
                        description: 'Номер договора',
                        type: 'string',
                        example: 'ДГ-2024-001'
                    ),
                    new OA\Property(
                        property: 'firstName',
                        description: 'Имя клиента',
                        type: 'string',
                        example: 'Иван'
                    ),
                    new OA\Property(
                        property: 'lastName',
                        description: 'Фамилия клиента',
                        type: 'string',
                        example: 'Иванов'
                    ),
                    new OA\Property(
                        property: 'middleName',
                        description: 'Отчество клиента',
                        type: 'string',
                        example: 'Иванович'
                    ),
                ],
                type: 'object'
            )
        ),
        tags: ['Contracts'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Контракт успешно создан',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'contractNumber', type: 'string', example: 'ДГ-2024-001'),
                        new OA\Property(property: 'firstName', type: 'string', example: 'Иван'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Иванов'),
                        new OA\Property(property: 'middleName', type: 'string', example: 'Иванович'),
                        new OA\Property(property: 'status', type: 'string', example: 'В работе'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Не указаны обязательные поля'),
                        new OA\Property(property: 'details', type: 'object'),
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
    public function create(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(data: ['error' => 'Пользователь не найден'], status: 401);
        }

        $data = json_decode(json: $request->getContent(), associative: true);

        if (!is_array($data)) {
            return $this->json(data: ['error' => 'Неверный формат данных'], status: 400);
        }

        $contractNumber = $data['contractNumber'] ?? null;
        $firstName = $data['firstName'] ?? null;
        $lastName = $data['lastName'] ?? null;
        $middleName = $data['middleName'] ?? null;

        $errors = [];

        if (empty($contractNumber)) {
            $errors['contractNumber'] = 'Номер договора обязателен';
        }

        if (empty($firstName)) {
            $errors['firstName'] = 'Имя клиента обязательно';
        }

        if (empty($lastName)) {
            $errors['lastName'] = 'Фамилия клиента обязательна';
        }

        if (empty($middleName)) {
            $errors['middleName'] = 'Отчество клиента обязательно';
        }

        if (!empty($errors)) {
            return $this->json(data: [
                'error' => 'Не указаны обязательные поля',
                'details' => $errors,
            ], status: 400);
        }

        $contract = new Contracts();
        $contract->setContractNumber($contractNumber);
        $contract->setFirstName($firstName);
        $contract->setLastName($lastName);
        $contract->setMiddleName($middleName);
        $contract->setAuthor($user);
        $contract->setStatus(ContractStatus::IN_PROGRESS);

        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        return $this->json(data: [
            'id' => $contract->getId(),
            'contractNumber' => $contract->getContractNumber(),
            'firstName' => $contract->getFirstName(),
            'lastName' => $contract->getLastName(),
            'middleName' => $contract->getMiddleName(),
            'status' => $contract->getStatus()->getLabel(),
        ], status: 201);
    }
}
