<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts;
use App\Entity\Enum\BankruptcyStage;
use App\Repository\ContractsRepository;
use App\Service\Serializer;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1')]
class ContractsController extends AbstractController
{
    public function __construct(
        private readonly ContractsRepository $contractsRepository,
    ) {
    }

    #[Route('/contracts', name: 'api_contracts_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: '/api/v1/contracts',
        summary: 'Получить список всех контрактов, сгруппированных по этапам банкротства',
        security: [['Bearer' => []]],
        tags: ['Contracts'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Поиск по ФИО или номеру дела (ID)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'page',
                description: 'Номер страницы',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Контракты, сгруппированные по этапам банкротства',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            description: 'Контракты, сгруппированные по этапам',
                            properties: [
                                new OA\Property(
                                    property: 'basic_info',
                                    description: 'Контракты с данными этапа "Основная информация"',
                                    type: 'array',
                                    items: new OA\Items(ref: new Model(type: Contracts::class, groups: ['basic_info'])),
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: 'pre_court',
                                    description: 'Контракты с данными этапа "Досудебка"',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: 'procedure_initiation',
                                    description: 'Контракты с данными этапа "Введение процедуры"',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                    nullable: true
                                ),
                                new OA\Property(
                                    property: 'procedure',
                                    description: 'Контракты с данными этапа "Процедура"',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                    nullable: true
                                ),
                            ],
                            type: 'object'
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
        $search = $request->query->get('search');
        $page = max(1, (int)($request->query->get('page') ?? 1));
        $limit = 20;

        $contracts = $this->contractsRepository->findAllOptimized(
            search: $search,
            page: $page,
            limit: $limit
        );
        $total = $this->contractsRepository->countAll(search: $search);

        $result = [];

        foreach (BankruptcyStage::cases() as $stage) {
            $groupName = $stage->value;

            try {
                $normalized = Serializer::normalize($contracts, null, ['groups' => [$groupName]]);

                if ($normalized !== null && $normalized !== []) {
                    $result[$stage->value] = $normalized;
                }
            } catch (\Exception) {
            }
        }

        $responseData = [
            'data' => $result,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => (int)ceil($total / $limit),
            ],
        ];

        return $this->json(data: $this->removeNullValues($responseData));
    }

    /**
     * Рекурсивно удаляет null значения из массива.
     */
    private function removeNullValues(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            } elseif (is_array($value)) {
                $data[$key] = $this->removeNullValues($value);
                if ($data[$key] === []) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }
}
