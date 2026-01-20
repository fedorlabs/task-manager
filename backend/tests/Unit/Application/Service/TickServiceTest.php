<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Dto\Query\TickQuery;
use App\Application\Dto\Tick\CreateTickRequest;
use App\Application\Dto\Tick\UpdateTickRequest;
use App\Application\Service\TickService;
use App\Domain\Entity\Task;
use App\Domain\Entity\Tick;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\TickRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TickServiceTest extends TestCase
{
    private TickRepositoryInterface&MockObject $tickRepository;
    private TaskRepositoryInterface&MockObject $taskRepository;
    private TickService $service;

    protected function setUp(): void
    {
        $this->tickRepository = $this->createMock(TickRepositoryInterface::class);
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);

        $this->service = new TickService(
            $this->tickRepository,
            $this->taskRepository,
        );
    }

    public function testGetTickByIdReturnsTick(): void
    {
        $tick = new Tick();
        $tick->setText('Found tick');

        $this->tickRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($tick);

        $result = $this->service->getTickById(1);
        $this->assertSame('Found tick', $result->getText());
    }

    public function testGetTickByIdThrowsNotFound(): void
    {
        $this->tickRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->service->getTickById(999);
    }

    public function testGetTicks(): void
    {
        $tick = new Tick();
        $tick->setText('Tick');

        $this->tickRepository->expects($this->once())
            ->method('findByFilters')
            ->willReturn([$tick]);

        $query = new TickQuery();
        $result = $this->service->getTicks($query);

        $this->assertCount(1, $result);
    }

    public function testGetTicksWithInvalidTaskThrowsNotFound(): void
    {
        $query = new TickQuery();
        $query->taskId = 999;

        $this->taskRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->getTicks($query);
    }

    public function testCreateTickWithTask(): void
    {
        $task = new Task();
        $task->setTitle('Parent')->setSortOrder(0);

        $this->taskRepository->method('findById')->with(5)->willReturn($task);
        $this->tickRepository->expects($this->once())->method('save');

        $dto = CreateTickRequest::fromArray([
            'text' => 'Tick',
            'taskId' => 5,
            'done' => true,
        ]);

        $tick = $this->service->createTick($dto);
        $this->assertSame($task, $tick->getTask());
    }

    public function testCreateTickWithMissingTaskThrowsValidationException(): void
    {
        $dto = CreateTickRequest::fromArray([
            'text' => 'Tick',
            'taskId' => null,
            'done' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Task is required');

        $this->service->createTick($dto);
    }

    public function testUpdateTick(): void
    {
        $tick = new Tick();
        $tick->setText('Old')->setDone(false);

        $this->tickRepository->method('findById')->with(1)->willReturn($tick);
        $this->tickRepository->expects($this->once())->method('save');

        $dto = UpdateTickRequest::fromArray([
            'text' => 'New',
            'done' => true,
        ]);

        $updated = $this->service->updateTick(1, $dto);
        $this->assertSame('New', $updated->getText());
    }

    public function testDeleteTick(): void
    {
        $tick = new Tick();
        $tick->setText('To Delete')->setDone(false);

        $this->tickRepository->method('findById')->with(1)->willReturn($tick);
        $this->tickRepository->expects($this->once())->method('remove')->with($tick);

        $this->service->deleteTick(1);
    }
}
