<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts;
use App\Entity\Enum\ContractStatus;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contracts>
 */
class ContractsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct(registry: $registry, entityClass: Contracts::class);
    }

    /**
     * Применить условия фильтрации к QueryBuilder.
     *
     * @param string|null $filter Фильтр: 'all', 'my', 'in_progress', 'completed'
     * @param User|null $user Текущий пользователь (для фильтра 'my')
     */
    private function applyFilterConditions(QueryBuilder $qb, ?string $filter, ?User $user = null): void
    {
        if ($filter === null || $filter === 'all') {
            return;
        }

        if ($filter === 'my') {
            if ($user !== null) {
                $qb->andWhere('c.author = :currentUser')
                    ->setParameter('currentUser', $user);
            }

            return;
        }

        if ($filter === 'in_progress') {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', ContractStatus::IN_PROGRESS->value);

            return;
        }

        if ($filter === 'completed') {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', ContractStatus::COMPLETED->value);

            return;
        }
    }

    /**
     * Применить поиск к QueryBuilder.
     *
     * @param QueryBuilder $qb QueryBuilder
     * @param string|null $search Поисковый запрос (по ФИО или номеру договора)
     */
    private function applySearch(QueryBuilder $qb, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $searchTerm = '%' . $search . '%';

        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->like('c.contractNumber', ':search'),
                $qb->expr()->like('c.firstName', ':search'),
                $qb->expr()->like('c.lastName', ':search'),
                $qb->expr()->like('c.middleName', ':search')
            )
        )->setParameter('search', $searchTerm);
    }

    /**
     * Применить сортировку к QueryBuilder.
     *
     * @param string|null $sortBy Поле для сортировки
     * @param string $sortOrder Направление сортировки ('ASC' или 'DESC')
     */
    private function applySorting(QueryBuilder $qb, ?string $sortBy, string $sortOrder = 'ASC'): void
    {
        $allowedFields = [
            'id',
            'contractNumber',
            'firstName',
            'lastName',
            'middleName',
            'contractDate',
            'status',
        ];

        if ($sortBy === null || !in_array($sortBy, $allowedFields, true)) {
            $qb->orderBy('c.id', 'DESC');

            return;
        }

        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        if ($sortBy === 'contractNumber') {
            $qb->orderBy('c.contractNumber', $sortOrder);
        } elseif ($sortBy === 'contractDate') {
            $qb->orderBy('c.contractDate', $sortOrder);
        } elseif ($sortBy === 'status') {
            $qb->orderBy('c.status', $sortOrder);
        } elseif ($sortBy === 'firstName' || $sortBy === 'lastName' || $sortBy === 'middleName') {
            $qb->orderBy('c.' . $sortBy, $sortOrder);
        } else {
            $qb->orderBy('c.id', $sortOrder);
        }
    }

    /**
     * Получить контракты с фильтрацией, сортировкой и пагинацией.
     *
     * @param string|null $filter Фильтр: 'all', 'my', 'in_progress', 'completed'
     * @param User|null $user Текущий пользователь (для фильтра 'my')
     * @param string|null $sortBy Поле для сортировки
     * @param string $sortOrder Направление сортировки ('ASC' или 'DESC')
     * @param int $page Номер страницы (начиная с 1)
     * @param int $limit Количество элементов на странице
     * @param string|null $search Поисковый запрос (по ФИО или номеру договора)
     *
     * @return array<int, Contracts>
     */
    public function findWithFilters(
        ?string $filter = null,
        ?User $user = null,
        ?string $sortBy = null,
        string $sortOrder = 'ASC',
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
    ): array {
        $qb = $this->createQueryBuilder(alias: 'c')
            ->leftJoin('c.author', 'author')
            ->leftJoin('c.manager', 'manager')
            ->addSelect('author')
            ->addSelect('manager');

        $this->applyFilterConditions(qb: $qb, filter: $filter, user: $user);
        $this->applySearch(qb: $qb, search: $search);
        $this->applySorting(qb: $qb, sortBy: $sortBy, sortOrder: $sortOrder);

        return $qb->setFirstResult(firstResult: ($page - 1) * $limit)
            ->setMaxResults(maxResults: $limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить количество контрактов по фильтру.
     *
     * @param string|null $filter Фильтр: 'all', 'my', 'in_progress', 'completed'
     * @param User|null $user Текущий пользователь (для фильтра 'my')
     * @param string|null $search Поисковый запрос (по ФИО или номеру договора)
     */
    public function countByFilter(?string $filter = null, ?User $user = null, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder(alias: 'c')
            ->select('COUNT(c.id)');

        $this->applyFilterConditions(qb: $qb, filter: $filter, user: $user);
        $this->applySearch(qb: $qb, search: $search);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
