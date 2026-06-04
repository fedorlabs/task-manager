<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Comment;

interface CommentRepositoryInterface
{
    public function findById(int $id): ?Comment;
    /** @return Comment[] */
    public function findAll(): array;
    /** @return Comment[] */
    public function findByTaskId(int $taskId): array;
    public function count(): int;
    public function save(Comment $comment): void;
    public function remove(Comment $comment): void;
}
