<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Status;

interface StatusRepositoryInterface
{
    public function findById(int $id): ?Status;
    /** @return Status[] */
    public function findAll(): array;
    /** @return Status[] */
    public function findPaginated(int $limit, int $offset, ?string $sort, string $order): array;
    public function count(): int;
    public function save(Status $status): void;
    public function remove(Status $status): void;
}
