<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\Query\UserQuery;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /** @return User[] */
    public function getAllUsers(): array
    {
        return $this->userRepository->findAll();
    }

    /** @return User[] */
    public function getUsers(UserQuery $query): array
    {
        return $this->userRepository->findPaginated($query->limit, $query->offset, $query->sort, $query->order ?? 'asc');
    }

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
