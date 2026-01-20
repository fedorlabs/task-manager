<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Exception;

use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\UnauthorizedException;
use App\Domain\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

final class DomainExceptionTest extends TestCase
{
    public function testDomainExceptionExtendsRuntimeException(): void
    {
        $exception = new DomainException('Test message', 500);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('Test message', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
    }

    public function testNotFoundExceptionIsDomainException(): void
    {
        $exception = new NotFoundException('Resource not found');

        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertSame('Resource not found', $exception->getMessage());
    }

    public function testValidationExceptionIsDomainException(): void
    {
        $exception = new ValidationException('Invalid input');

        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertSame('Invalid input', $exception->getMessage());
    }

    public function testAccessDeniedExceptionIsDomainException(): void
    {
        $exception = new AccessDeniedException('Forbidden');

        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertSame('Forbidden', $exception->getMessage());
    }

    public function testUnauthorizedExceptionIsDomainException(): void
    {
        $exception = new UnauthorizedException('Unauthorized');

        $this->assertInstanceOf(DomainException::class, $exception);
        $this->assertSame('Unauthorized', $exception->getMessage());
    }
}
