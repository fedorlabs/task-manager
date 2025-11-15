<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Status;

interface StatusRepositoryInterface
{
    public function findById(int $id): ?Status;
    /** @return Status[] */
    public function findAll(): array;
    public function count(): int;
    public function save(Status $status): void;
    public function remove(Status $status): void;
}
