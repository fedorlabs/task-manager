<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreateUserGeneratesUuid(): void
    {
        $user = new User();

        $this->assertNotEmpty($user->getId());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $user->getId(),
        );
    }

    public function testSetAndGetName(): void
    {
        $user = new User();
        $user->setName('John Doe');

        $this->assertSame('John Doe', $user->getName());
    }

    public function testSetAndGetEmail(): void
    {
        $user = new User();
        $user->setEmail('john@example.com');

        $this->assertSame('john@example.com', $user->getEmail());
    }

    public function testIsAdminDefaultFalse(): void
    {
        $user = new User();

        $this->assertFalse($user->isAdmin());
    }

    public function testSetIsAdmin(): void
    {
        $user = new User();
        $user->setIsAdmin(true);

        $this->assertTrue($user->isAdmin());
    }

    public function testGetRolesForRegularUser(): void
    {
        $user = new User();

        $this->assertSame(['ROLE_USER'], $user->getRoles());
    }

    public function testGetRolesForAdminUser(): void
    {
        $user = new User();
        $user->setIsAdmin(true);

        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->assertSame('test@example.com', $user->getUserIdentifier());
    }

    public function testAvatarDefaultNull(): void
    {
        $user = new User();

        $this->assertNull($user->getAvatar());
    }

    public function testSetAvatar(): void
    {
        $user = new User();
        $user->setAvatar('/public/avatar.jpg');

        $this->assertSame('/public/avatar.jpg', $user->getAvatar());
    }

    public function testFluentInterface(): void
    {
        $user = new User();
        $result = $user->setName('Test')
            ->setEmail('test@test.com')
            ->setPassword('hashed')
            ->setIsAdmin(false)
            ->setAvatar('/avatar.jpg');

        $this->assertInstanceOf(User::class, $result);
    }

    public function testEmptyCollectionsOnCreate(): void
    {
        $user = new User();

        $this->assertCount(0, $user->getTasks());
        $this->assertCount(0, $user->getComments());
    }
}
