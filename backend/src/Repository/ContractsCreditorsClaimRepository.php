<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContractsCreditorsClaim;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContractsCreditorsClaim>
 */
class ContractsCreditorsClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractsCreditorsClaim::class);
    }
}

