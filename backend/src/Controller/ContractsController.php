<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contracts;
use App\Entity\Enum\ContractStatus;
use App\Entity\User;
use App\Repository\ContractsRepository;
use App\Service\ContractorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1')]
#[IsGranted('ROLE_USER')]
class ContractsController extends AbstractController
{
    #[Route('/contracts', name: 'api_contracts_list', methods: ['GET'])]
    public function list(
        Request $request,
        ContractsRepository $contractsRepository,
    ): JsonResponse {
        /** @var User|null $user */
        $user = $this->getUser();

        $filter = $request->query->get('filter', 'all');
        $sortBy = $request->query->get('sortBy');
        $sortOrder = $request->query->get('sortOrder', 'ASC');
        $page = max(1, (int)($request->query->get('page') ?? 1));
        $limit = min(100, max(1, (int)($request->query->get('limit') ?? 20)));
        $search = $request->query->get('search');

        $contracts = $contractsRepository->findWithFilters(
            filter: $filter,
            user: $user,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
            page: $page,
            limit: $limit,
            search: $search
        );

        $total = $contractsRepository->countByFilter(filter: $filter, user: $user, search: $search);

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
            'all' => $contractsRepository->countByFilter(filter: 'all', user: $user),
            'my' => $contractsRepository->countByFilter(filter: 'my', user: $user),
            'in_progress' => $contractsRepository->countByFilter(filter: 'in_progress', user: $user),
            'completed' => $contractsRepository->countByFilter(filter: 'completed', user: $user),
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
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
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

        $entityManager->persist($contract);
        $entityManager->flush();

        return $this->json(data: [
            'id' => $contract->getId(),
            'contractNumber' => $contract->getContractNumber(),
            'firstName' => $contract->getFirstName(),
            'lastName' => $contract->getLastName(),
            'middleName' => $contract->getMiddleName(),
            'status' => $contract->getStatus()->getLabel(),
        ], status: Response::HTTP_CREATED);
    }

    #[Route('/contracts/{id}', name: 'api_contracts_show', methods: ['GET'])]
    public function show(
        int $id,
        ContractsRepository $contractsRepository,
        ContractorService $contractorService,
    ): JsonResponse {
        $contract = $contractsRepository->find($id);

        if (!$contract instanceof Contracts) {
            return $this->json(data: ['error' => 'Контракт не найден'], status: Response::HTTP_NOT_FOUND);
        }

        return $this->json(data: $contractorService->serializeContractByStages(contract: $contract));
    }

    #[Route('/contracts/{id}', name: 'api_contracts_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        ContractsRepository $contractsRepository,
        ContractorService $contractorService,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $contract = $contractsRepository->find($id);

        if (!$contract instanceof Contracts) {
            return $this->json(data: ['error' => 'Контракт не найден'], status: 404);
        }

        $bankruptcyStages = json_decode(json: $request->getContent(), associative: true);

        if (!is_array($bankruptcyStages)) {
            return $this->json(data: ['error' => 'Неверный формат данных'], status: 400);
        }

        foreach ($bankruptcyStages as $bankruptcyStageData) {
            $contractorService->updateContractFields(contract: $contract, data: $bankruptcyStageData);
        }

        $entityManager->flush();

        return $this->json(data: $contractorService->serializeContractByStages(contract: $contract));
    }

    #[Route('/contracts/{id}', name: 'api_contracts_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(
        int $id,
        ContractsRepository $contractsRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $contract = $contractsRepository->find($id);

        if (!$contract instanceof Contracts) {
            return $this->json(data: ['error' => 'Контракт не найден'], status: 404);
        }

        $entityManager->remove($contract);
        $entityManager->flush();

        return $this->json(data: [], status: 204);
    }
}
