<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Repository\TaskRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Infrastructure\Doctrine\Repository\RepositoryOrderTrait;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Task> */
class DoctrineTaskRepository extends ServiceEntityRepository implements TaskRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findById(int $id): ?Task
    {
        return $this->find($id);
    }

    /** @return Task[] */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /** @return Task[] */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /** @return Task[] */
    public function findByFilters(
        ?User $user,
        ?int $columnId,
        ?int $statusId,
        ?string $q,
        ?string $sort,
        string $order,
        int $limit,
        int $offset
    ): array
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.column', 'c')
            ->addSelect('c')
            ->leftJoin('t.status', 's')
            ->addSelect('s')
            ->leftJoin('t.user', 'u')
            ->addSelect('u');

        if ($user) {
            $qb->andWhere('t.user = :user')->setParameter('user', $user);
        }
        if ($columnId !== null) {
            $qb
                ->andWhere('c.id = :columnId')
                ->setParameter('columnId', $columnId);
        }
        if ($statusId !== null) {
            $qb
                ->andWhere('s.id = :statusId')
                ->setParameter('statusId', $statusId);
        }
        if ($q !== null) {
            $qb
                ->andWhere('LOWER(t.title) LIKE :q OR LOWER(t.description) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        $sortField = match ($sort) {
            'title' => 't.title',
            'createdAt' => 't.createdAt',
            'updatedAt' => 't.updatedAt',
            'dueDate' => 't.dueDate',
            'sortOrder' => 't.sortOrder',
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

    public function countByUser(User $user): int
    {
        return parent::count(['user' => $user]);
    }

    public function countByFilters(?User $user, ?int $columnId, ?int $statusId, ?string $q): int
    {
        $qb = $this->createQueryBuilder('t')->select('COUNT(t.id)');

        if ($user) {
            $qb->andWhere('t.user = :user')->setParameter('user', $user);
        }
        if ($columnId !== null) {
            $qb
                ->join('t.column', 'c')
                ->andWhere('c.id = :columnId')
                ->setParameter('columnId', $columnId);
        }
        if ($statusId !== null) {
            $qb
                ->join('t.status', 's')
                ->andWhere('s.id = :statusId')
                ->setParameter('statusId', $statusId);
        }
        if ($q !== null) {
            $qb
                ->andWhere('LOWER(t.title) LIKE :q OR LOWER(t.description) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function remove(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
