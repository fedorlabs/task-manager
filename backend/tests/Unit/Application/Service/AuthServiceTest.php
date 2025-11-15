<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\AuthService;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AuthServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private UserPasswordHasherInterface&MockObject $passwordHasher;
    private AuthService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->service = new AuthService($this->userRepository, $this->passwordHasher);
    }

    public function testRegisterCreatesUser(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed_password');
        $this->userRepository->expects($this->once())->method('save');

        $user = $this->service->register('John', 'john@example.com', 'password123');

        $this->assertSame('John', $user->getName());
        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertFalse($user->isAdmin());
    }

    public function testRegisterAdminUser(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $this->userRepository->expects($this->once())->method('save');

        $user = $this->service->register('Admin', 'admin@example.com', 'admin', true, '/avatar.jpg');

        $this->assertTrue($user->isAdmin());
        $this->assertSame('/avatar.jpg', $user->getAvatar());
    }

    public function testRegisterThrowsWhenEmailExists(): void
    {
        $existing = new User();
        $existing->setEmail('taken@example.com');

        $this->userRepository->method('findByEmail')->willReturn($existing);

        $this->expectException(BadRequestHttpException::class);

        $this->service->register('Jane', 'taken@example.com', 'password');
    }

    public function testGetCurrentUserReturnsUser(): void
    {
        $user = new User();
        $user->setName('Found User')->setEmail('found@example.com');

        $this->userRepository->method('findById')->with('uuid-123')->willReturn($user);

        $result = $this->service->getCurrentUser('uuid-123');

        $this->assertSame('Found User', $result->getName());
    }

    public function testGetCurrentUserThrowsWhenNotFound(): void
    {
        $this->userRepository->method('findById')->willReturn(null);

        $this->expectException(UnauthorizedHttpException::class);

        $this->service->getCurrentUser('nonexistent-uuid');
    }
}
