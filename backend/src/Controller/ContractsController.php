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
        responses: [
            new OA\Response(
                response: 200,
                description: 'Контракты, сгруппированные по этапам банкротства',
                content: new OA\JsonContent(
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
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Неавторизован'
            ),
        ]
    )]
    public function list(): JsonResponse
    {
        $contracts = $this->contractsRepository->findAllOptimized();

        $result = [];
        foreach (BankruptcyStage::cases() as $stage) {
            $groupName = $stage->value;

            try {
                $normalized = Serializer::normalize($contracts, null, ['groups' => [$groupName]]);
                $result[$stage->value] = $normalized;
            } catch (\Exception) {
                $result[$stage->value] = null;
            }
        }

        return $this->json(data: $result);
    }
}
