<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Column;
use App\Domain\Repository\ColumnRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
