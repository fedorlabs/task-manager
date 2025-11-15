<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Tick;
use App\Domain\Repository\TickRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineTickRepository extends ServiceEntityRepository implements TickRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tick::class);
    }

    public function findById(int $id): ?Tick
    {
        return $this->find($id);
    }

    /** @return Tick[] */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /** @return Tick[] */
    public function findByTaskId(int $taskId): array
    {
        return $this->findBy(['task' => $taskId]);
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function save(Tick $tick): void
    {
        $this->getEntityManager()->persist($tick);
        $this->getEntityManager()->flush();
    }

    public function remove(Tick $tick): void
    {
        $this->getEntityManager()->remove($tick);
        $this->getEntityManager()->flush();
    }
}
