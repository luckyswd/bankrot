<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DocumentTemplate;
use App\Entity\Enum\BankruptcyStage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Создаёт QueryBuilder с фильтрами по поиску и категории.
     *
     * @param string|null $search Поиск по названию шаблона
     * @param BankruptcyStage|null $category Фильтр по категории
     */
    public function createSearchQueryBuilder(?string $search = null, ?BankruptcyStage $category = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('dt');

        if ($search !== null && $search !== '') {
            $qb->andWhere('dt.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($category !== null) {
            $qb->andWhere('dt.category = :category')
                ->setParameter('category', $category);
        }

        $qb->orderBy('dt.name', 'ASC');

        return $qb;
    }

    /**
     * Находит шаблоны с пагинацией.
     *
     * @param int $page Номер страницы (начиная с 1)
     * @param int $limit Количество элементов на странице
     * @param string|null $search Поиск по названию шаблона
     * @param BankruptcyStage|null $category Фильтр по категории
     *
     * @return array{items: DocumentTemplate[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginated(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
        ?BankruptcyStage $category = null,
    ): array {
        $qb = $this->createSearchQueryBuilder($search, $category);

        // Подсчёт общего количества
        $countQb = clone $qb;
        $total = (int)$countQb->select('COUNT(dt.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Получение элементов для страницы
        $offset = ($page - 1) * $limit;
        $items = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $pages = (int)ceil($total / $limit);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $pages,
        ];
    }
}
