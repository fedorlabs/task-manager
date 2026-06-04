<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function register(string $name, string $email, string $password, bool $isAdmin = false, ?string $avatar = null): User
    {
        $existing = $this->userRepository->findByEmail($email);
        if ($existing) {
            throw new BadRequestHttpException('An error occurred during registration');
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setIsAdmin($isAdmin);
        $user->setAvatar($avatar);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->save($user);

        return $user;
    }

    public function getCurrentUser(string $userId): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'The user is not authorized');
        }
        return $user;
    }
}
