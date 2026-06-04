<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Comment;
use App\Domain\Entity\User;
use App\Domain\Repository\CommentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Infrastructure\Doctrine\Repository\RepositoryOrderTrait;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Comment> */
class DoctrineCommentRepository extends ServiceEntityRepository implements CommentRepositoryInterface
{
    use RepositoryOrderTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findById(int $id): ?Comment
    {
        return $this->find($id);
    }

    /** @return Comment[] */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /** @return Comment[] */
    public function findByTaskId(int $taskId): array
    {
        return $this->findBy(['task' => $taskId]);
    }

    /** @return Comment[] */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user]);
    }

    /** @return Comment[] */
    public function findByFilters(
        ?User $user,
        ?int $taskId,
        ?string $q,
        ?string $sort,
        string $order,
        int $limit,
        int $offset
    ): array
    {
        $qb = $this->createQueryBuilder('c');

        if ($user) {
            $qb->andWhere('c.user = :user')->setParameter('user', $user);
        }
        if ($taskId !== null) {
            $qb
                ->join('c.task', 't')
                ->andWhere('t.id = :taskId')
                ->setParameter('taskId', $taskId);
        }
        if ($q !== null) {
            $qb
                ->andWhere('LOWER(c.text) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        $sortField = match ($sort) {
            'createdAt' => 'c.createdAt',
            'updatedAt' => 'c.updatedAt',
            default => 'c.id',
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

    public function countByFilters(?User $user, ?int $taskId, ?string $q): int
    {
        $qb = $this->createQueryBuilder('c')->select('COUNT(c.id)');

        if ($user) {
            $qb->andWhere('c.user = :user')->setParameter('user', $user);
        }
        if ($taskId !== null) {
            $qb
                ->join('c.task', 't')
                ->andWhere('t.id = :taskId')
                ->setParameter('taskId', $taskId);
        }
        if ($q !== null) {
            $qb
                ->andWhere('LOWER(c.text) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function save(Comment $comment): void
    {
        $this->getEntityManager()->persist($comment);
        $this->getEntityManager()->flush();
    }

    public function remove(Comment $comment): void
    {
        $this->getEntityManager()->remove($comment);
        $this->getEntityManager()->flush();
    }
}
