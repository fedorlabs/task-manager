<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\Auth\SignupRequest;
use App\Domain\Entity\User;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function register(SignupRequest $dto): User
    {
        $existing = $this->userRepository->findByEmail($dto->email);
        if ($existing) {
            throw new ValidationException('Email is already in use');
        }

        $user = new User();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setIsAdmin(false);
        $user->setAvatar($dto->avatar);
        $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));

        $this->userRepository->save($user);

        return $user;
    }

    public function getCurrentUser(string $userId): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('User not found');
        }
        return $user;
    }
}
