<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentTemplate>
 */
class DocumentTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentTemplate::class);
    }

    /**
     * Находит шаблон по имени и категории.
     */
    public function findByNameAndCategory(string $name, BankruptcyStage $category): ?DocumentTemplate
    {
        return $this->createQueryBuilder('dt')
            ->where('dt.name = :name')
            ->andWhere('dt.category = :category')
            ->setParameter('name', $name)
            ->setParameter('category', $category)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
