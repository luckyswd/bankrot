<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Court;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Court>
 */
class CourtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Court::class);
    }

    /**
     * Создаёт QueryBuilder с фильтрами по поиску.
     *
     * @param string|null $search Поиск по наименованию, адресу
     */
    public function createSearchQueryBuilder(?string $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        if ($search !== null && $search !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.name', ':search'),
                    $qb->expr()->like('c.address', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('c.name', 'ASC');

        return $qb;
    }

    /**
     * Находит суды с пагинацией.
     *
     * @param int $page Номер страницы (начиная с 1)
     * @param int $limit Количество элементов на странице
     * @param string|null $search Поиск по наименованию, адресу
     *
     * @return array{items: Court[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginated(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
    ): array {
        $qb = $this->createSearchQueryBuilder($search);

        // Подсчёт общего количества
        $countQb = clone $qb;
        $total = (int)$countQb->select('COUNT(c.id)')
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
