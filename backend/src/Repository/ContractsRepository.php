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
     * Получить все контракты с оптимизированным запросом
     *
     * @return array<int, Contracts>
     */
    public function findAllOptimized(): array
    {
        return $this->createQueryBuilder(alias: 'c')
            ->select('c')
            ->getQuery()
            ->getResult();
    }
}
