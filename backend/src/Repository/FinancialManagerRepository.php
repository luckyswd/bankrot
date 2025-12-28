<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\FinancialManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FinancialManager>
 */
class FinancialManagerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinancialManager::class);
    }

    /**
     * Создаёт QueryBuilder с фильтрами по поиску.
     *
     * @param string|null $search Поиск по ФИО, ИНН, СНИЛС, email
     */
    public function createSearchQueryBuilder(?string $search = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('fm');

        if ($search !== null && $search !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('fm.fio', ':search'),
                    $qb->expr()->like('fm.inn', ':search'),
                    $qb->expr()->like('fm.snils', ':search'),
                    $qb->expr()->like('fm.email', ':search')
                )
            )
                ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('fm.fio', 'ASC');

        return $qb;
    }

    /**
     * Находит финансовых управляющих с пагинацией.
     *
     * @param int $page Номер страницы (начиная с 1)
     * @param int $limit Количество элементов на странице
     * @param string|null $search Поиск по ФИО, ИНН, СНИЛС, email
     *
     * @return array{items: FinancialManager[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginated(
        int $page = 1,
        int $limit = 10,
        ?string $search = null,
    ): array {
        $qb = $this->createSearchQueryBuilder($search);

        // Подсчёт общего количества
        $countQb = clone $qb;
        $total = (int)$countQb->select('COUNT(fm.id)')
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
