<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Task;
use App\Domain\Entity\User;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?Task;
    /** @return Task[] */
    public function findAll(): array;
    /** @return Task[] */
    public function findByUser(User $user): array;
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
    ): array;
    public function count(): int;
    public function countByUser(User $user): int;
    public function countByFilters(?User $user, ?int $columnId, ?int $statusId, ?string $q): int;
    public function save(Task $task): void;
    public function remove(Task $task): void;
}
