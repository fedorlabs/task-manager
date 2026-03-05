<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Tick;
use App\Domain\Repository\TickRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Infrastructure\Doctrine\Repository\RepositoryOrderTrait;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Tick> */
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

    /** @return Tick[] */
    public function findByFilters(?int $taskId, ?string $sort, string $order, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('t');

        if ($taskId !== null) {
            $qb
                ->join('t.task', 'task')
                ->andWhere('task.id = :taskId')
                ->setParameter('taskId', $taskId);
        }

        $sortField = match ($sort) {
            'text' => 't.text',
            'done' => 't.done',
            default => 't.id',
        };

        return $qb
            ->orderBy($sortField, $this->normalizeOrder($order))
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function countByFilters(?int $taskId): int
    {
        $qb = $this->createQueryBuilder('t')->select('COUNT(t.id)');

        if ($taskId !== null) {
            $qb
                ->join('t.task', 'task')
                ->andWhere('task.id = :taskId')
                ->setParameter('taskId', $taskId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
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
