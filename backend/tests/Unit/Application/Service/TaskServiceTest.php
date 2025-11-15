<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\TaskService;
use App\Domain\Entity\Column;
use App\Domain\Entity\Status;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Repository\ColumnRepositoryInterface;
use App\Domain\Repository\StatusRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface&MockObject $taskRepository;
    private ColumnRepositoryInterface&MockObject $columnRepository;
    private StatusRepositoryInterface&MockObject $statusRepository;
    private UserRepositoryInterface&MockObject $userRepository;
    private TaskService $service;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->columnRepository = $this->createMock(ColumnRepositoryInterface::class);
        $this->statusRepository = $this->createMock(StatusRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->service = new TaskService(
            $this->taskRepository,
            $this->columnRepository,
            $this->statusRepository,
            $this->userRepository,
        );
    }

    public function testGetAllTasks(): void
    {
        $task1 = new Task();
        $task1->setTitle('Task 1');
        $task2 = new Task();
        $task2->setTitle('Task 2');

        $this->taskRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$task1, $task2]);

        $tasks = $this->service->getAllTasks();

        $this->assertCount(2, $tasks);
        $this->assertSame('Task 1', $tasks[0]->getTitle());
    }

    public function testGetTaskByIdReturnsTask(): void
    {
        $task = new Task();
        $task->setTitle('Found Task');

        $this->taskRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($task);

        $result = $this->service->getTaskById(1);

        $this->assertSame('Found Task', $result->getTitle());
    }

    public function testGetTaskByIdThrowsNotFound(): void
    {
        $this->taskRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->service->getTaskById(999);
    }

    public function testGetTaskCount(): void
    {
        $this->taskRepository->expects($this->once())
            ->method('count')
            ->willReturn(42);

        $this->assertSame(42, $this->service->getTaskCount());
    }

    public function testCreateTask(): void
    {
        $this->taskRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Task::class));

        $task = $this->service->createTask([
            'title' => 'New Task',
            'description' => 'Description',
            'sortOrder' => 0,
        ]);

        $this->assertSame('New Task', $task->getTitle());
        $this->assertSame('Description', $task->getDescription());
        $this->assertSame(0, $task->getSortOrder());
    }

    public function testCreateTaskWithRelations(): void
    {
        $column = new Column();
        $column->setTitle('In Progress');
        $status = new Status();
        $status->setName('Active');
        $user = new User();
        $user->setName('John');

        $this->columnRepository->method('findById')->with(1)->willReturn($column);
        $this->statusRepository->method('findById')->with(2)->willReturn($status);
        $this->userRepository->method('findById')->with('uuid-123')->willReturn($user);
        $this->taskRepository->expects($this->once())->method('save');

        $task = $this->service->createTask([
            'title' => 'Task with relations',
            'sortOrder' => 1,
            'columnId' => 1,
            'statusId' => 2,
            'userId' => 'uuid-123',
        ]);

        $this->assertSame($column, $task->getColumn());
        $this->assertSame($status, $task->getStatus());
        $this->assertSame($user, $task->getUser());
    }

    public function testUpdateTask(): void
    {
        $task = new Task();
        $task->setTitle('Old Title')->setSortOrder(0);

        $this->taskRepository->method('findById')->with(1)->willReturn($task);
        $this->taskRepository->expects($this->once())->method('save');

        $updated = $this->service->updateTask(1, ['title' => 'New Title']);

        $this->assertSame('New Title', $updated->getTitle());
    }

    public function testDeleteTask(): void
    {
        $task = new Task();
        $task->setTitle('To Delete')->setSortOrder(0);

        $this->taskRepository->method('findById')->with(1)->willReturn($task);
        $this->taskRepository->expects($this->once())
            ->method('remove')
            ->with($task);

        $this->service->deleteTask(1);
    }

    public function testDeleteTaskNotFoundThrows(): void
    {
        $this->taskRepository->method('findById')->with(999)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->service->deleteTask(999);
    }

    public function testCreateTaskWithNullRelations(): void
    {
        $this->taskRepository->expects($this->once())->method('save');

        $task = $this->service->createTask([
            'title' => 'Orphan Task',
            'sortOrder' => 0,
            'columnId' => null,
            'statusId' => null,
            'userId' => null,
        ]);

        $this->assertNull($task->getColumn());
        $this->assertNull($task->getStatus());
        $this->assertNull($task->getUser());
    }
}
