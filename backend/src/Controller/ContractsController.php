<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts;
use App\Entity\Enum\BankruptcyStage;
use App\Entity\Enum\ContractStatus;
use App\Entity\User;
use App\Repository\ContractsRepository;
use App\Repository\BailiffRepository;
use App\Repository\CourtRepository;
use App\Repository\CreditorRepository;
use App\Repository\FnsRepository;
use App\Repository\GostekhnadzorRepository;
use App\Repository\MchsRepository;
use App\Repository\DocumentTemplateRepository;
use App\Repository\RosgvardiaRepository;
use App\Service\Serializer;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class ContractsController extends AbstractController
{
    public function __construct(
        private readonly ContractsRepository $contractsRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly DocumentTemplateRepository $documentTemplateRepository,
        private readonly CourtRepository $courtRepository,
        private readonly CreditorRepository $creditorRepository,
        private readonly MchsRepository $mchsRepository,
        private readonly GostekhnadzorRepository $gostekhnadzorRepository,
        private readonly FnsRepository $fnsRepository,
        private readonly BailiffRepository $bailiffRepository,
        private readonly RosgvardiaRepository $rosgvardiaRepository,
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
                schema: new OA\Schema(type: Types::STRING, default: 'all', enum: ['all', 'my', 'in_progress', 'completed'])
            ),
            new OA\Parameter(
                name: 'sortBy',
                description: 'Поле для сортировки: id, contractNumber, firstName, lastName, middleName, contractDate, status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: Types::STRING)
            ),
            new OA\Parameter(
                name: 'sortOrder',
                description: 'Направление сортировки: ASC или DESC',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: Types::STRING, default: 'ASC', enum: ['ASC', 'DESC'])
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
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по ФИО или номеру договора',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: Types::STRING, example: 'Иванов')
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
                                    new OA\Property(property: 'contractNumber', type: Types::STRING, nullable: true),
                                    new OA\Property(property: 'fullName', type: Types::STRING, nullable: true),
                                    new OA\Property(property: 'contractDate', type: Types::STRING, format: 'date', nullable: true),
                                    new OA\Property(property: 'manager', type: 'object', nullable: true),
                                    new OA\Property(property: 'author', type: 'object', nullable: true),
                                    new OA\Property(property: 'status', type: Types::STRING),
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
        $search = $request->query->get('search');

        $contracts = $this->contractsRepository->findWithFilters(
            filter: $filter,
            user: $user,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
            page: $page,
            limit: $limit,
            search: $search
        );

        $total = $this->contractsRepository->countByFilter(filter: $filter, user: $user, search: $search);

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
                        type: Types::STRING,
                        example: 'ДГ-2024-001'
                    ),
                    new OA\Property(
                        property: 'firstName',
                        description: 'Имя клиента',
                        type: Types::STRING,
                        example: 'Иван'
                    ),
                    new OA\Property(
                        property: 'lastName',
                        description: 'Фамилия клиента',
                        type: Types::STRING,
                        example: 'Иванов'
                    ),
                    new OA\Property(
                        property: 'middleName',
                        description: 'Отчество клиента',
                        type: Types::STRING,
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
                        new OA\Property(property: 'contractNumber', type: Types::STRING, example: 'ДГ-2024-001'),
                        new OA\Property(property: 'firstName', type: Types::STRING, example: 'Иван'),
                        new OA\Property(property: 'lastName', type: Types::STRING, example: 'Иванов'),
                        new OA\Property(property: 'middleName', type: Types::STRING, example: 'Иванович'),
                        new OA\Property(property: 'status', type: Types::STRING, example: 'В работе'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: Types::STRING, example: 'Не указаны обязательные поля'),
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

    #[Route('/contracts/{id}', name: 'api_contracts_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/v1/contracts/{id}',
        summary: 'Получить контракт по ID',
        security: [['Bearer' => []]],
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID контракта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Данные контракта, сгруппированные по группам сериализации. Каждая группа содержит поле documents - массив доступных документов для данной стадии.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'basic_info',
                            description: 'Основная информация о контракте. Содержит поле documents - массив документов для стадии "Основная информация".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Основная информация"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'pre_court',
                            description: 'Досудебка. Содержит поле documents - массив документов для стадии "Досудебка".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Досудебка"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'judicial',
                            description: 'Судебка. Содержит поле documents - массив документов для стадии "Судебка".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Судебка"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'realization',
                            description: 'Реализация. Содержит поле documents - массив документов для стадии "Реализация".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Реализация"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'procedure_initiation',
                            description: 'Введение процедуры. Содержит поле documents - массив документов для стадии "Введение процедуры".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Введение процедуры"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'procedure',
                            description: 'Процедура. Содержит поле documents - массив документов для стадии "Процедура".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Процедура"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'report',
                            description: 'Отчет. Содержит поле documents - массив документов для стадии "Отчет".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Отчет"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                    ],
                    type: 'object',
                    example: [
                        'basic_info' => [
                            'id' => 1,
                            'contractNumber' => 'ДГ-2024-001',
                            'firstName' => 'Иван',
                            'lastName' => 'Иванов',
                            'middleName' => 'Иванович',
                            'status' => 'В работе',
                            'documents' => [
                                ['id' => 1, 'name' => 'Заявление о признании банкротом'],
                                ['id' => 2, 'name' => 'Документ 2'],
                            ],
                        ],
                        'pre_court' => [
                            'documents' => [
                                ['id' => 3, 'name' => 'Досудебное уведомление'],
                            ],
                        ],
                        'judicial' => [],
                        'realization' => [
                            'documents' => [
                                ['id' => 4, 'name' => 'Документ реализации'],
                            ],
                        ],
                        'procedure_initiation' => [
                            'documents' => [
                                ['id' => 5, 'name' => 'Документ введения процедуры'],
                            ],
                        ],
                        'procedure' => [
                            'documents' => [
                                ['id' => 6, 'name' => 'Документ процедуры'],
                            ],
                        ],
                        'report' => [
                            'documents' => [
                                ['id' => 7, 'name' => 'Отчет'],
                            ],
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Контракт не найден'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $contract = $this->contractsRepository->find($id);

        if (!$contract instanceof Contracts) {
            return $this->json(data: ['error' => 'Контракт не найден'], status: 404);
        }

        return $this->json(data: $this->serializeContractByStages(contract: $contract));
    }

    #[Route('/contracts/{id}', name: 'api_contracts_update', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/v1/contracts/{id}',
        summary: 'Обновить контракт',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Данные для обновления контракта, сгруппированные по группам сериализации (обновляются только переданные поля)',
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'basic_info',
                        description: 'Основная информация о контракте',
                        type: 'object',
                        additionalProperties: true
                    ),
                    new OA\Property(
                        property: 'pre_court',
                        description: 'Досудебка',
                        type: 'object',
                        additionalProperties: true
                    ),
                    new OA\Property(
                        property: 'judicial',
                        description: 'Судебка',
                        type: 'object',
                        additionalProperties: true
                    ),
                    new OA\Property(
                        property: 'realization',
                        description: 'Реализация',
                        type: 'object',
                        additionalProperties: true
                    ),
                    new OA\Property(
                        property: 'procedure_initiation',
                        description: 'Введение процедуры',
                        type: 'object',
                        additionalProperties: true
                    ),
                    new OA\Property(
                        property: 'procedure',
                        description: 'Процедура',
                        type: 'object',
                        additionalProperties: true
                    ),
                    new OA\Property(
                        property: 'report',
                        description: 'Отчет',
                        type: 'object',
                        additionalProperties: true
                    ),
                ],
                type: 'object',
                example: [
                    'basic_info' => [
                        'firstName' => 'Иван',
                        'lastName' => 'Иванов',
                        'middleName' => 'Иванович',
                    ],
                ]
            )
        ),
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID контракта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Контракт успешно обновлен. Возвращаются данные контракта, сгруппированные по группам сериализации. Каждая группа содержит поле documents - массив доступных документов для данной стадии.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'basic_info',
                            description: 'Основная информация о контракте. Содержит поле documents - массив документов для стадии "Основная информация".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Основная информация"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'pre_court',
                            description: 'Досудебка. Содержит поле documents - массив документов для стадии "Досудебка".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Досудебка"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'judicial',
                            description: 'Судебка. Содержит поле documents - массив документов для стадии "Судебка".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Судебка"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'realization',
                            description: 'Реализация. Содержит поле documents - массив документов для стадии "Реализация".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Реализация"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'procedure_initiation',
                            description: 'Введение процедуры. Содержит поле documents - массив документов для стадии "Введение процедуры".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Введение процедуры"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'procedure',
                            description: 'Процедура. Содержит поле documents - массив документов для стадии "Процедура".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Процедура"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer', description: 'ID шаблона документа'),
                                            new OA\Property(property: 'name', type: Types::STRING, description: 'Название документа'),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                        new OA\Property(
                            property: 'report',
                            description: 'Отчет. Содержит поле documents - массив документов для стадии "Отчет".',
                            properties: [
                                new OA\Property(
                                    property: 'documents',
                                    description: 'Массив документов для стадии "Отчет"',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', description: 'ID шаблона документа', type: 'integer'),
                                            new OA\Property(property: 'name', description: 'Название документа', type: Types::STRING),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object',
                            additionalProperties: true
                        ),
                    ],
                    type: 'object',
                    example: [
                        'basic_info' => [
                            'id' => 1,
                            'contractNumber' => 'ДГ-2024-001',
                            'firstName' => 'Иван',
                            'lastName' => 'Иванов',
                            'middleName' => 'Иванович',
                            'status' => 'В работе',
                            'documents' => [
                                ['id' => 1, 'name' => 'Заявление о признании банкротом'],
                                ['id' => 2, 'name' => 'Документ 2'],
                            ],
                        ],
                        'pre_court' => [
                            'documents' => [
                                ['id' => 3, 'name' => 'Досудебное уведомление'],
                            ],
                        ],
                        'judicial' => [],
                        'realization' => [
                            'documents' => [
                                ['id' => 4, 'name' => 'Документ реализации'],
                            ],
                        ],
                        'procedure_initiation' => [
                            'documents' => [
                                ['id' => 5, 'name' => 'Документ введения процедуры'],
                            ],
                        ],
                        'procedure' => [
                            'documents' => [
                                ['id' => 6, 'name' => 'Документ процедуры'],
                            ],
                        ],
                        'report' => [
                            'documents' => [
                                ['id' => 7, 'name' => 'Отчет'],
                            ],
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Ошибка валидации'
            ),
            new OA\Response(
                response: 404,
                description: 'Контракт не найден'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $contract = $this->contractsRepository->find($id);

        if (!$contract instanceof Contracts) {
            return $this->json(data: ['error' => 'Контракт не найден'], status: 404);
        }

        $bankruptcyStages = json_decode(json: $request->getContent(), associative: true);

        if (!is_array($bankruptcyStages)) {
            return $this->json(data: ['error' => 'Неверный формат данных'], status: 400);
        }

        foreach ($bankruptcyStages as $bankruptcyStageData) {
            $this->updateContractFields(contract: $contract, data: $bankruptcyStageData);
        }

        $this->entityManager->flush();

        return $this->json(data: $this->serializeContractByStages(contract: $contract));
    }

    #[Route('/contracts/{id}', name: 'api_contracts_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: '/api/v1/contracts/{id}',
        summary: 'Удалить контракт',
        security: [['Bearer' => []]],
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID контракта',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Контракт успешно удален'
            ),
            new OA\Response(
                response: 404,
                description: 'Контракт не найден'
            ),
            new OA\Response(
                response: 403,
                description: 'Доступ запрещен (требуется роль ROLE_ADMIN)'
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $contract = $this->contractsRepository->find($id);

        if (!$contract instanceof Contracts) {
            return $this->json(data: ['error' => 'Контракт не найден'], status: 404);
        }

        $this->entityManager->remove($contract);
        $this->entityManager->flush();

        return $this->json(data: [], status: 204);
    }

    /**
     * Сериализует контракт, группируя данные по стадиям банкротства.
     *
     * @return array<string, mixed>
     */
    private function serializeContractByStages(Contracts $contract): array
    {
        $allTemplates = $this->documentTemplateRepository->findAll();

        $documentsByStage = [];

        foreach ($allTemplates as $template) {
            $stageValue = $template->getCategory()->value;

            if (!isset($documentsByStage[$stageValue])) {
                $documentsByStage[$stageValue] = [];
            }

            $documentsByStage[$stageValue][] = [
                'id' => $template->getId(),
                'name' => $template->getName(),
            ];
        }

        $result = [];

        foreach (BankruptcyStage::cases() as $stage) {
            $normalized = Serializer::normalize(data: $contract, context: ['groups' => $stage->value]);

            if (is_array($normalized)) {
                $filtered = $this->filterNullValues(data: $normalized);
                $result[$stage->value] = $filtered;
            } else {
                $result[$stage->value] = [];
            }

            $result[$stage->value]['documents'] = $documentsByStage[$stage->value] ?? [];
        }

        return $result;
    }

    /**
     * Обновляет поля контракта только для переданных ключей динамически.
     *
     * @param array<string, mixed> $data
     */
    private function updateContractFields(Contracts $contract, array $data): void
    {
        $reflection = new \ReflectionClass($contract);
        $dateFields = [
            'birthDate',
            'passportIssuedDate',
            'spouseBirthDate',
            'contractDate',
            'powerOfAttorneyDate',
            'procedureInitiationDecisionDate',
            'procedureInitiationResolutionDate',
            'procedureInitiationDocumentDate',
        ];
        $dateTimeFields = [
            'hearingDateTime',
            'efrsbDateTime',
            'marriageTerminationDate',
        ];

        foreach ($data as $key => $value) {
            if ($key === 'court') {
                if ($value === null) {
                    $contract->setCourt(null);
                } elseif (is_numeric($value)) {
                    $court = $this->courtRepository->find((int)$value);

                    if ($court !== null) {
                        $contract->setCourt($court);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationMchs') {
                if ($value === null) {
                    $contract->setProcedureInitiationMchs(null);
                } elseif (is_numeric($value)) {
                    $mchs = $this->mchsRepository->find((int)$value);

                    if ($mchs !== null) {
                        $contract->setProcedureInitiationMchs($mchs);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationGostekhnadzor') {
                if ($value === null) {
                    $contract->setProcedureInitiationGostekhnadzor(null);
                } elseif (is_numeric($value)) {
                    $gostekhnadzor = $this->gostekhnadzorRepository->find((int)$value);

                    if ($gostekhnadzor !== null) {
                        $contract->setProcedureInitiationGostekhnadzor($gostekhnadzor);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationFns') {
                if ($value === null) {
                    $contract->setProcedureInitiationFns(null);
                } elseif (is_numeric($value)) {
                    $fns = $this->fnsRepository->find((int)$value);

                    if ($fns !== null) {
                        $contract->setProcedureInitiationFns($fns);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationBailiff') {
                if ($value === null) {
                    $contract->setProcedureInitiationBailiff(null);
                } elseif (is_numeric($value)) {
                    $bailiff = $this->bailiffRepository->find((int)$value);

                    if ($bailiff !== null) {
                        $contract->setProcedureInitiationBailiff($bailiff);
                    }
                }

                continue;
            }

            if ($key === 'procedureInitiationRosgvardia') {
                if ($value === null) {
                    $contract->setProcedureInitiationRosgvardia(null);
                } elseif (is_numeric($value)) {
                    $rosgvardia = $this->rosgvardiaRepository->find((int)$value);

                    if ($rosgvardia !== null) {
                        $contract->setProcedureInitiationRosgvardia(procedureInitiationRosgvardia: $rosgvardia);
                    }
                }

                continue;
            }

            if ($key === 'creditors') {
                $contract->getCreditors()->clear();

                if (is_array($value)) {
                    foreach ($value as $creditorId) {
                        if (is_numeric($creditorId)) {
                            $creditor = $this->creditorRepository->find((int)$creditorId);

                            if ($creditor) {
                                $contract->addCreditor($creditor);
                            }
                        }
                    }
                }

                continue;
            }

            $setterName = 'set' . ucfirst($key);

            if (!$reflection->hasMethod($setterName)) {
                continue;
            }

            $method = $reflection->getMethod($setterName);

            if (!$method->isPublic()) {
                continue;
            }

            // Обработка полей типа date
            if (in_array($key, $dateFields, true) && is_string($value)) {
                $method->invoke($contract, new \DateTime($value));

                continue;
            }

            // Обработка полей типа datetime
            if (in_array($key, $dateTimeFields, true) && is_string($value)) {
                $method->invoke($contract, new \DateTime($value));

                continue;
            }

            // Обработка статуса
            if ($key === 'status' && $value !== null) {
                $method->invoke($contract, ContractStatus::from($value));

                continue;
            }

            $method->invoke($contract, $value);
        }
    }

    /**
     * Фильтрует null значения из массива рекурсивно.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function filterNullValues(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                $nested = $this->filterNullValues($value);

                if (!empty($nested)) {
                    $filtered[$key] = $nested;
                }
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
