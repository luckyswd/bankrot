<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContractsPreCourtCreditor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContractsPreCourtCreditor>
 */
class ContractsPreCourtCreditorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractsPreCourtCreditor::class);
    }
}
