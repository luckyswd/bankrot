<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts;
use App\Repository\ContractsRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_USER')]
class ContractsController extends AbstractController
{
    public function __construct(
        private readonly ContractsRepository $contractsRepository,
    ) {
    }

    #[Route('/contracts', name: 'api_contracts_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/contracts',
        summary: 'Получить список всех контрактов',
        security: [['Bearer' => []]],
        tags: ['Contracts'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список всех контрактов с полной информацией',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: Contracts::class)
                    )
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

        return $this->json(data: $contracts, context: ['groups' => ['contracts:read']]);
    }
}
