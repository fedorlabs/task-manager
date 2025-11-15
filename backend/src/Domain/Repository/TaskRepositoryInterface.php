<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Task;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?Task;
    /** @return Task[] */
    public function findAll(): array;
    public function count(): int;
    public function save(Task $task): void;
    public function remove(Task $task): void;
}
