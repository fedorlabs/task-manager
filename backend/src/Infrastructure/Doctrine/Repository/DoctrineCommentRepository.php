<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Comment;
use App\Domain\Repository\CommentRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineCommentRepository extends ServiceEntityRepository implements CommentRepositoryInterface
{
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

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
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
