<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\TickService;
use App\Domain\Entity\Task;
use App\Domain\Entity\Tick;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\TickRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function testGetAllTicks(): void
    {
        $t1 = new Tick();
        $t1->setText('Tick 1');
        $t2 = new Tick();
        $t2->setText('Tick 2');

        $this->tickRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$t1, $t2]);

        $result = $this->service->getAllTicks();

        $this->assertCount(2, $result);
        $this->assertSame('Tick 1', $result[0]->getText());
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

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Tick not found');

        $this->service->getTickById(999);
    }

    public function testGetTicksByTaskId(): void
    {
        $tick = new Tick();
        $tick->setText('Task tick');

        $this->tickRepository->expects($this->once())
            ->method('findByTaskId')
            ->with(7)
            ->willReturn([$tick]);

        $result = $this->service->getTicksByTaskId(7);

        $this->assertCount(1, $result);
        $this->assertSame('Task tick', $result[0]->getText());
    }

    public function testGetTickCount(): void
    {
        $this->tickRepository->expects($this->once())
            ->method('count')
            ->willReturn(30);

        $this->assertSame(30, $this->service->getTickCount());
    }

    public function testCreateTick(): void
    {
        $this->tickRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Tick::class));

        $tick = $this->service->createTick([
            'text' => 'New tick',
            'done' => false,
        ]);

        $this->assertSame('New tick', $tick->getText());
        $this->assertFalse($tick->isDone());
    }

    public function testCreateTickWithTaskRelation(): void
    {
        $task = new Task();
        $task->setTitle('Parent task')->setSortOrder(0);

        $this->taskRepository->method('findById')->with(5)->willReturn($task);
        $this->tickRepository->expects($this->once())->method('save');

        $tick = $this->service->createTick([
            'text' => 'Tick with task',
            'done' => true,
            'taskId' => 5,
        ]);

        $this->assertSame($task, $tick->getTask());
        $this->assertTrue($tick->isDone());
    }

    public function testCreateTickWithNullTaskId(): void
    {
        $this->tickRepository->expects($this->once())->method('save');

        $tick = $this->service->createTick([
            'text' => 'Orphan tick',
            'taskId' => null,
        ]);

        $this->assertNull($tick->getTask());
    }

    public function testUpdateTick(): void
    {
        $tick = new Tick();
        $tick->setText('Old text')->setDone(false);

        $this->tickRepository->method('findById')->with(1)->willReturn($tick);
        $this->tickRepository->expects($this->once())->method('save');

        $updated = $this->service->updateTick(1, [
            'text' => 'Updated text',
            'done' => true,
        ]);

        $this->assertSame('Updated text', $updated->getText());
        $this->assertTrue($updated->isDone());
    }

    public function testDeleteTick(): void
    {
        $tick = new Tick();
        $tick->setText('To delete');

        $this->tickRepository->method('findById')->with(1)->willReturn($tick);
        $this->tickRepository->expects($this->once())
            ->method('remove')
            ->with($tick);

        $this->service->deleteTick(1);
    }

    public function testDeleteTickNotFoundThrows(): void
    {
        $this->tickRepository->method('findById')->with(999)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->service->deleteTick(999);
    }
}
