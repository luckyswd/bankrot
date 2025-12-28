<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\FinancialManager;
use App\Repository\FinancialManagerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/financial-managers')]
#[IsGranted('ROLE_USER')]
class FinancialManagerController extends AbstractController
{
    public function __construct(
        private readonly FinancialManagerRepository $financialManagerRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'api_financial_managers_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $search = $request->query->get('search') ?? '';
        $limitParam = $request->query->get('limit') ?? '10';
        $page = max(1, (int)($request->query->get('page') ?? 1));

        if ($limitParam === 'all') {
            $qb = $this->financialManagerRepository->createSearchQueryBuilder($search);
            $items = $qb->getQuery()->getResult();
            $total = count($items);

            $result = [];

            foreach ($items as $financialManager) {
                $result[] = $this->serializeFinancialManager($financialManager);
            }

            return $this->json([
                'items' => $result,
                'total' => $total,
                'page' => 1,
                'limit' => $total,
                'pages' => 1,
            ]);
        }

        $limit = max(1, min(100, (int)$limitParam));

        $result = $this->financialManagerRepository->findPaginated(
            page: $page,
            limit: $limit,
            search: $search
        );

        $data = [];

        foreach ($result['items'] as $financialManager) {
            $data[] = $this->serializeFinancialManager($financialManager);
        }

        return $this->json([
            'items' => $data,
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'pages' => $result['pages'],
        ]);
    }

    #[Route('/{id}', name: 'api_financial_managers_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $financialManager = $this->financialManagerRepository->find($id);

        if (!$financialManager instanceof FinancialManager) {
            return $this->json(data: ['error' => 'Финансовый управляющий не найден'], status: 404);
        }

        return $this->json(data: $this->serializeFinancialManagerFull($financialManager));
    }

    #[Route('', name: 'api_financial_managers_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['fio']) || empty(trim($data['fio']))) {
            return $this->json(data: ['error' => 'ФИО обязательно'], status: 400);
        }

        $financialManager = new FinancialManager();
        $this->setFinancialManagerFields($financialManager, $data);

        $this->entityManager->persist($financialManager);
        $this->entityManager->flush();

        return $this->json(
            data: $this->serializeFinancialManagerFull($financialManager),
            status: 201
        );
    }

    #[Route('/{id}', name: 'api_financial_managers_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $financialManager = $this->financialManagerRepository->find($id);

        if (!$financialManager instanceof FinancialManager) {
            return $this->json(data: ['error' => 'Финансовый управляющий не найден'], status: 404);
        }

        $data = json_decode($request->getContent(), true);

        $this->setFinancialManagerFields($financialManager, $data);

        $this->entityManager->flush();

        return $this->json(data: $this->serializeFinancialManagerFull($financialManager));
    }

    #[Route('/{id}', name: 'api_financial_managers_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $financialManager = $this->financialManagerRepository->find($id);

        if (!$financialManager instanceof FinancialManager) {
            return $this->json(data: ['error' => 'Финансовый управляющий не найден'], status: 404);
        }

        $this->entityManager->remove($financialManager);
        $this->entityManager->flush();

        return $this->json(data: [], status: 204);
    }

    private function setFinancialManagerFields(FinancialManager $financialManager, array $data): void
    {
        if (isset($data['fio'])) {
            $financialManager->setFio(!empty(trim($data['fio'])) ? trim($data['fio']) : null);
        }

        if (isset($data['fioGenitive'])) {
            $financialManager->setFioGenitive(!empty(trim($data['fioGenitive'])) ? trim($data['fioGenitive']) : null);
        }

        if (isset($data['inn'])) {
            $financialManager->setInn(!empty(trim($data['inn'])) ? trim($data['inn']) : null);
        }

        if (isset($data['snils'])) {
            $financialManager->setSnils(!empty(trim($data['snils'])) ? trim($data['snils']) : null);
        }

        if (isset($data['arbitrationManagerRegistryNumber'])) {
            $financialManager->setArbitrationManagerRegistryNumber(!empty(trim($data['arbitrationManagerRegistryNumber'])) ? trim($data['arbitrationManagerRegistryNumber']) : null);
        }

        if (isset($data['email'])) {
            $financialManager->setEmail(!empty(trim($data['email'])) ? trim($data['email']) : null);
        }

        if (isset($data['phone'])) {
            $financialManager->setPhone(!empty(trim($data['phone'])) ? trim($data['phone']) : null);
        }

        if (isset($data['aauName'])) {
            $financialManager->setAauName(!empty(trim($data['aauName'])) ? trim($data['aauName']) : null);
        }

        if (isset($data['aauOgrn'])) {
            $financialManager->setAauOgrn(!empty(trim($data['aauOgrn'])) ? trim($data['aauOgrn']) : null);
        }

        if (isset($data['aauInn'])) {
            $financialManager->setAauInn(!empty(trim($data['aauInn'])) ? trim($data['aauInn']) : null);
        }

        if (isset($data['aauAddress'])) {
            $financialManager->setAauAddress(!empty(trim($data['aauAddress'])) ? trim($data['aauAddress']) : null);
        }
    }

    private function serializeFinancialManager(FinancialManager $financialManager): array
    {
        return [
            'id' => $financialManager->getId(),
            'fio' => $financialManager->getFio(),
            'inn' => $financialManager->getInn(),
            'snils' => $financialManager->getSnils(),
            'email' => $financialManager->getEmail(),
            'phone' => $financialManager->getPhone(),
        ];
    }

    private function serializeFinancialManagerFull(FinancialManager $financialManager): array
    {
        return [
            'id' => $financialManager->getId(),
            'fio' => $financialManager->getFio(),
            'fioGenitive' => $financialManager->getFioGenitive(),
            'inn' => $financialManager->getInn(),
            'snils' => $financialManager->getSnils(),
            'arbitrationManagerRegistryNumber' => $financialManager->getArbitrationManagerRegistryNumber(),
            'email' => $financialManager->getEmail(),
            'phone' => $financialManager->getPhone(),
            'aauName' => $financialManager->getAauName(),
            'aauOgrn' => $financialManager->getAauOgrn(),
            'aauInn' => $financialManager->getAauInn(),
            'aauAddress' => $financialManager->getAauAddress(),
        ];
    }
}
