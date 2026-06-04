<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Comment;
use App\Domain\Entity\User;

interface CommentRepositoryInterface
{
    public function findById(int $id): ?Comment;
    /** @return Comment[] */
    public function findAll(): array;
    /** @return Comment[] */
    public function findByTaskId(int $taskId): array;
    /** @return Comment[] */
    public function findByUser(User $user): array;
    /** @return Comment[] */
    public function findByFilters(
        ?User $user,
        ?int $taskId,
        ?string $q,
        ?string $sort,
        string $order,
        int $limit,
        int $offset
    ): array;
    public function count(): int;
    public function countByUser(User $user): int;
    public function countByFilters(?User $user, ?int $taskId, ?string $q): int;
    public function save(Comment $comment): void;
    public function remove(Comment $comment): void;
}
