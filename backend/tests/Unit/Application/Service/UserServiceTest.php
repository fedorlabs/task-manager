<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\UserService;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UserServiceTest extends TestCase
{
    private UserRepositoryInterface&MockObject $userRepository;
    private UserService $service;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->service = new UserService($this->userRepository);
    }

    public function testGetAllUsers(): void
    {
        $u1 = new User();
        $u1->setName('Alice');
        $u2 = new User();
        $u2->setName('Bob');

        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$u1, $u2]);

        $result = $this->service->getAllUsers();

        $this->assertCount(2, $result);
        $this->assertSame('Alice', $result[0]->getName());
        $this->assertSame('Bob', $result[1]->getName());
    }

    public function testGetUserByIdReturnsUser(): void
    {
        $user = new User();
        $user->setName('Charlie');

        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('uuid-123')
            ->willReturn($user);

        $result = $this->service->getUserById('uuid-123');

        $this->assertNotNull($result);
        $this->assertSame('Charlie', $result->getName());
    }

    public function testGetUserByIdReturnsNullWhenNotFound(): void
    {
        $this->userRepository->expects($this->once())
            ->method('findById')
            ->with('uuid-unknown')
            ->willReturn(null);

        $result = $this->service->getUserById('uuid-unknown');

        $this->assertNull($result);
    }
}
