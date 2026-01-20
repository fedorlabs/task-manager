<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Dto\Auth\SignupRequest;
use App\Application\Service\AuthService;
use App\Domain\Entity\User;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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

        $dto = SignupRequest::fromArray([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $user = $this->service->register($dto);

        $this->assertSame('John', $user->getName());
        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertFalse($user->isAdmin());
    }

    public function testRegisterSetsAvatar(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $this->userRepository->expects($this->once())->method('save');

        $dto = SignupRequest::fromArray([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'admin1234',
            'avatar' => '/avatar.jpg',
        ]);
        $user = $this->service->register($dto);

        $this->assertFalse($user->isAdmin());
        $this->assertSame('/avatar.jpg', $user->getAvatar());
    }

    public function testRegisterThrowsWhenEmailExists(): void
    {
        $existing = new User();
        $existing->setEmail('taken@example.com');

        $this->userRepository->method('findByEmail')->willReturn($existing);

        $this->expectException(ValidationException::class);

        $dto = SignupRequest::fromArray([
            'name' => 'Jane',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);
        $this->service->register($dto);
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

        $this->expectException(NotFoundException::class);

        $this->service->getCurrentUser('nonexistent-uuid');
    }
}
