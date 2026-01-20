<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Dto\Task;

use App\Application\Dto\Task\CreateTaskRequest;
use PHPUnit\Framework\TestCase;

final class CreateTaskRequestTest extends TestCase
{
    public function testFromArrayWithAllFields(): void
    {
        $dto = CreateTaskRequest::fromArray([
            'title' => '  Test Task  ',
            'description' => 'Description',
            'sortOrder' => 5,
            'dueDate' => '2026-12-31',
            'url' => 'https://example.com',
            'urlDescription' => 'Example',
            'tags' => 'tag1#tag2',
            'columnId' => 1,
            'statusId' => 2,
        ]);

        $this->assertSame('Test Task', $dto->title);
        $this->assertSame('Description', $dto->description);
        $this->assertSame(5, $dto->sortOrder);
        $this->assertSame('2026-12-31', $dto->dueDate);
        $this->assertSame('https://example.com', $dto->url);
        $this->assertSame('Example', $dto->urlDescription);
        $this->assertSame('tag1#tag2', $dto->tags);
        $this->assertSame(1, $dto->columnId);
        $this->assertSame(2, $dto->statusId);
    }

    public function testFromArrayWithMinimalData(): void
    {
        $dto = CreateTaskRequest::fromArray([
            'title' => 'Minimal',
        ]);

        $this->assertSame('Minimal', $dto->title);
        $this->assertNull($dto->description);
        $this->assertNull($dto->sortOrder);
        $this->assertNull($dto->columnId);
        $this->assertNull($dto->statusId);
    }

    public function testFromArrayWithInvalidIntReturnsMinusOne(): void
    {
        $dto = CreateTaskRequest::fromArray([
            'title' => 'Test',
            'columnId' => 'invalid',
        ]);

        $this->assertSame(-1, $dto->columnId);
    }

    public function testFromArrayWithEmptyStringReturnsNull(): void
    {
        $dto = CreateTaskRequest::fromArray([
            'title' => 'Test',
            'columnId' => '',
        ]);

        $this->assertNull($dto->columnId);
    }
}
