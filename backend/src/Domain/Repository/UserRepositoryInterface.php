<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;
    public function findByEmail(string $email): ?User;
    /** @return User[] */
    public function findAll(): array;
    /** @return User[] */
    public function findPaginated(int $limit, int $offset, ?string $sort, string $order): array;
    public function save(User $user): void;
    public function remove(User $user): void;
}
