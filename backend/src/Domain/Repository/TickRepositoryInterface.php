<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Tick;

interface TickRepositoryInterface
{
    public function findById(int $id): ?Tick;
    /** @return Tick[] */
    public function findAll(): array;
    /** @return Tick[] */
    public function findByTaskId(int $taskId): array;
    /** @return Tick[] */
    public function findByFilters(?int $taskId, ?string $sort, string $order, int $limit, int $offset): array;
    public function count(): int;
    public function countByFilters(?int $taskId): int;
    public function save(Tick $tick): void;
    public function remove(Tick $tick): void;
}
