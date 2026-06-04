<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Dto\Task;

use App\Application\Dto\Task\UpdateTaskRequest;
use PHPUnit\Framework\TestCase;

final class UpdateTaskRequestTest extends TestCase
{
    public function testFromArrayTracksProvidedFields(): void
    {
        $dto = UpdateTaskRequest::fromArray([
            'title' => 'New Title',
            'description' => 'New Desc',
        ]);

        $this->assertTrue($dto->isProvided('title'));
        $this->assertTrue($dto->isProvided('description'));
        $this->assertFalse($dto->isProvided('sortOrder'));
        $this->assertFalse($dto->isProvided('columnId'));
    }

    public function testFromArrayWithNullValueTracksAsProvided(): void
    {
        $dto = UpdateTaskRequest::fromArray([
            'title' => null,
        ]);

        $this->assertTrue($dto->isProvided('title'));
        // When null is passed, trim((string) null) becomes ''
        $this->assertSame('', $dto->title);
    }

    public function testFromArrayWithMissingFieldNotTracked(): void
    {
        $dto = UpdateTaskRequest::fromArray([]);

        $this->assertFalse($dto->isProvided('title'));
        $this->assertFalse($dto->isProvided('description'));
        $this->assertNull($dto->title);
    }

    public function testFromArrayTrimsTitle(): void
    {
        $dto = UpdateTaskRequest::fromArray([
            'title' => '  Trimmed  ',
        ]);

        $this->assertSame('Trimmed', $dto->title);
    }

    public function testIsProvidedReturnsFalseForUnknownField(): void
    {
        $dto = UpdateTaskRequest::fromArray([]);

        $this->assertFalse($dto->isProvided('unknown'));
    }
}
