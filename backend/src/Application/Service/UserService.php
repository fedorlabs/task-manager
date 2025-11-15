<?php

declare(strict_types=1);

namespace App\Application\Service;

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

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
