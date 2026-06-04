<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Serializer;

use App\Domain\Entity\User;
use App\Infrastructure\Serializer\UserNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UserNormalizerTest extends TestCase
{
    private UserNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new UserNormalizer();
        $this->normalizer->setNormalizer($this->createMock(NormalizerInterface::class));
    }

    public function testNormalizeUser(): void
    {
        $user = new User();
        $user->setName('John')
            ->setEmail('john@example.com')
            ->setIsAdmin(true)
            ->setAvatar('/avatar.jpg')
            ->setPassword('hashed_password');

        $data = $this->normalizer->normalize($user);

        $this->assertSame('John', $data['name']);
        $this->assertSame('john@example.com', $data['email']);
        $this->assertTrue($data['isAdmin']);
        $this->assertSame('/avatar.jpg', $data['avatar']);
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('roles', $data);
    }

    public function testSupportsNormalization(): void
    {
        $user = new User();
        $this->assertTrue($this->normalizer->supportsNormalization($user));
        $this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testGetSupportedTypes(): void
    {
        $types = $this->normalizer->getSupportedTypes(null);
        $this->assertArrayHasKey(User::class, $types);
        $this->assertTrue($types[User::class]);
    }
}
