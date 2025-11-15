<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ColumnService;
use App\Domain\Entity\Column;
use App\Domain\Repository\ColumnRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ColumnServiceTest extends TestCase
{
    private ColumnRepositoryInterface&MockObject $repository;
    private ColumnService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ColumnRepositoryInterface::class);
        $this->service = new ColumnService($this->repository);
    }

    public function testGetAllColumns(): void
    {
        $col1 = new Column();
        $col1->setTitle('Scheduled');
        $col2 = new Column();
        $col2->setTitle('Done');

        $this->repository->method('findAll')->willReturn([$col1, $col2]);

        $columns = $this->service->getAllColumns();

        $this->assertCount(2, $columns);
    }

    public function testGetColumnByIdReturnsColumn(): void
    {
        $column = new Column();
        $column->setTitle('In Progress');

        $this->repository->method('findById')->with(1)->willReturn($column);

        $this->assertSame('In Progress', $this->service->getColumnById(1)->getTitle());
    }

    public function testGetColumnByIdThrowsNotFound(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->service->getColumnById(999);
    }

    public function testCreateColumn(): void
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Column::class));

        $column = $this->service->createColumn('New Column');

        $this->assertSame('New Column', $column->getTitle());
    }

    public function testUpdateColumn(): void
    {
        $column = new Column();
        $column->setTitle('Old Title');

        $this->repository->method('findById')->with(1)->willReturn($column);
        $this->repository->expects($this->once())->method('save');

        $updated = $this->service->updateColumn(1, ['title' => 'Updated']);

        $this->assertSame('Updated', $updated->getTitle());
    }

    public function testDeleteColumn(): void
    {
        $column = new Column();
        $column->setTitle('To Delete');

        $this->repository->method('findById')->with(1)->willReturn($column);
        $this->repository->expects($this->once())->method('remove')->with($column);

        $this->service->deleteColumn(1);
    }

    public function testGetColumnCount(): void
    {
        $this->repository->method('count')->willReturn(5);

        $this->assertSame(5, $this->service->getColumnCount());
    }
}
