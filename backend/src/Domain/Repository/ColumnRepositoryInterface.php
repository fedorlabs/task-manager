<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Column;

interface ColumnRepositoryInterface
{
    public function findById(int $id): ?Column;
    /** @return Column[] */
    public function findAll(): array;
    /** @return Column[] */
    public function findPaginated(int $limit, int $offset, ?string $sort, string $order): array;
    public function count(): int;
    public function save(Column $column): void;
    public function remove(Column $column): void;
}
