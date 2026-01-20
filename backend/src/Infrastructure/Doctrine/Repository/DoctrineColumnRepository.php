<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Column;
use App\Domain\Repository\ColumnRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Column> */
class DoctrineColumnRepository extends ServiceEntityRepository implements ColumnRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Column::class);
    }

    public function findById(int $id): ?Column
    {
        return $this->find($id);
    }

    /** @return Column[] */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /** @return Column[] */
    public function findPaginated(int $limit, int $offset, ?string $sort, string $order): array
    {
        $sortField = match ($sort) {
            'title' => 'c.title',
            default => 'c.id',
        };

        return $this->createQueryBuilder('c')
            ->orderBy($sortField, $this->normalizeOrder($order))
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    private function normalizeOrder(string $order): string
    {
        return strtolower($order) === 'desc' ? 'DESC' : 'ASC';
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function save(Column $column): void
    {
        $this->getEntityManager()->persist($column);
        $this->getEntityManager()->flush();
    }

    public function remove(Column $column): void
    {
        $this->getEntityManager()->remove($column);
        $this->getEntityManager()->flush();
    }
}
