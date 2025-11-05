<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Contracts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * Добавить условия поиска к QueryBuilder.
     *
     * @param string|null $search Поиск по ФИО или номеру дела (ID)
     */
    private function applySearchConditions(\Doctrine\ORM\QueryBuilder $qb, ?string $search): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $searchTerm = '%' . $search . '%';
        $searchId = is_numeric($search) ? (int)$search : null;

        $orConditions = $qb->expr()->orX(
            $qb->expr()->like('c.firstName', ':search'),
            $qb->expr()->like('c.lastName', ':search'),
            $qb->expr()->like('c.middleName', ':search')
        );

        if ($searchId !== null) {
            $orConditions->add($qb->expr()->eq('c.id', ':searchId'));
            $qb->setParameter('searchId', $searchId);
        }

        $qb->where($orConditions)
            ->setParameter('search', $searchTerm);
    }

    /**
     * Получить все контракты с оптимизированным запросом
     *
     * @param string|null $search Поиск по ФИО или номеру дела (ID)
     * @param int         $page   Номер страницы (начиная с 1)
     * @param int         $limit  Количество элементов на странице
     *
     * @return array<int, Contracts>
     */
    public function findAllOptimized(?string $search = null, int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder(alias: 'c');

        $this->applySearchConditions($qb, $search);

        return $qb->orderBy('c.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Получить общее количество контрактов.
     *
     * @param string|null $search Поиск по ФИО или номеру дела (ID)
     */
    public function countAll(?string $search = null): int
    {
        $qb = $this->createQueryBuilder(alias: 'c')
            ->select('COUNT(c.id)');

        $this->applySearchConditions($qb, $search);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
