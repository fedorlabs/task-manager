<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Dto\Column\CreateColumnRequest;
use App\Application\Dto\Column\UpdateColumnRequest;
use App\Application\Dto\Query\ColumnQuery;
use App\Application\Service\ColumnService;
use App\Domain\Entity\Column;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\ColumnRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ColumnServiceTest extends TestCase
{
    private ColumnRepositoryInterface&MockObject $columnRepository;
    private ColumnService $service;

    protected function setUp(): void
    {
        $this->columnRepository = $this->createMock(ColumnRepositoryInterface::class);
        $this->service = new ColumnService($this->columnRepository);
    }

    public function testGetColumnByIdReturnsColumn(): void
    {
        $column = new Column();
        $column->setTitle('Column');

        $this->columnRepository->method('findById')->with(1)->willReturn($column);

        $result = $this->service->getColumnById(1);
        $this->assertSame('Column', $result->getTitle());
    }

    public function testGetColumnByIdThrowsNotFound(): void
    {
        $this->columnRepository->method('findById')->with(999)->willReturn(null);
        $this->expectException(NotFoundException::class);

        $this->service->getColumnById(999);
    }

    public function testGetColumns(): void
    {
        $column = new Column();
        $column->setTitle('Column');

        $this->columnRepository->expects($this->once())
            ->method('findPaginated')
            ->willReturn([$column]);

        $query = new ColumnQuery();
        $result = $this->service->getColumns($query);

        $this->assertCount(1, $result);
    }

    public function testCreateColumn(): void
    {
        $this->columnRepository->expects($this->once())->method('save');

        $dto = CreateColumnRequest::fromArray(['title' => 'New']);
        $column = $this->service->createColumn($dto);

        $this->assertSame('New', $column->getTitle());
    }

    public function testUpdateColumn(): void
    {
        $column = new Column();
        $column->setTitle('Old');

        $this->columnRepository->method('findById')->with(1)->willReturn($column);
        $this->columnRepository->expects($this->once())->method('save');

        $dto = UpdateColumnRequest::fromArray(['title' => 'New']);
        $updated = $this->service->updateColumn(1, $dto);

        $this->assertSame('New', $updated->getTitle());
    }
}
