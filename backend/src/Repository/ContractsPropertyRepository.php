<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ContractsProperty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContractsProperty>
 */
class ContractsPropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContractsProperty::class);
    }
}
