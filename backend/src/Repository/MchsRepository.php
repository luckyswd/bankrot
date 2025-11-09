<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Mchs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mchs>
 */
class MchsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mchs::class);
    }

    /**
     * Создаёт QueryBuilder с фильтрами по поиску.
     *
     * @param string|null $search Поиск по наименованию, адресу, телефону
     */
    public function createSearchQueryBuilder(?string $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');

        if ($search !== null && $search !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('m.name', ':search'),
                    $qb->expr()->like('m.address', ':search'),
                    $qb->expr()->like('m.phone', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('m.name', 'ASC');

        return $qb;
    }

    /**
     * Находит отделения ГИМС МЧС с пагинацией.
     *
     * @param int $page Номер страницы (начиная с 1)
     * @param int $limit Количество элементов на странице
     * @param string|null $search Поиск по наименованию, адресу, телефону
     *
     * @return array{items: Mchs[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginated(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
    ): array {
        $qb = $this->createSearchQueryBuilder($search);

        // Подсчёт общего количества
        $countQb = clone $qb;
        $total = (int)$countQb->select('COUNT(m.id)')
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
