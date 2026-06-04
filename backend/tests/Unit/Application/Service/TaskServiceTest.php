<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Dto\Query\TaskQuery;
use App\Application\Dto\Task\CreateTaskRequest;
use App\Application\Dto\Task\UpdateTaskRequest;
use App\Application\Service\TaskService;
use App\Domain\Entity\Column;
use App\Domain\Entity\Status;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\ColumnRepositoryInterface;
use App\Domain\Repository\StatusRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TaskServiceTest extends TestCase
{
    private TaskRepositoryInterface&MockObject $taskRepository;
    private ColumnRepositoryInterface&MockObject $columnRepository;
    private StatusRepositoryInterface&MockObject $statusRepository;
    private TaskService $service;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->columnRepository = $this->createMock(ColumnRepositoryInterface::class);
        $this->statusRepository = $this->createMock(StatusRepositoryInterface::class);

        $this->service = new TaskService(
            $this->taskRepository,
            $this->columnRepository,
            $this->statusRepository,
        );
    }

    public function testGetTaskByIdReturnsTask(): void
    {
        $task = new Task();
        $task->setTitle('Found Task')->setSortOrder(0);

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

        $this->expectException(NotFoundException::class);

        $this->service->getTaskById(999);
    }

    public function testGetTasksForUser(): void
    {
        $user = new User();
        $user->setName('User')->setEmail('u@example.com');

        $task = new Task();
        $task->setTitle('Task')->setSortOrder(0);

        $this->taskRepository->expects($this->once())
            ->method('findByFilters')
            ->willReturn([$task]);

        $query = new TaskQuery();
        $tasks = $this->service->getTasksForUser($user, $query);

        $this->assertCount(1, $tasks);
    }

    public function testGetTasksForUserWithInvalidColumnThrowsValidationException(): void
    {
        $user = new User();
        $user->setName('User')->setEmail('u@example.com');

        $query = new TaskQuery();
        $query->columnId = 999;

        $this->columnRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Column not found');

        $this->service->getTasksForUser($user, $query);
    }

    public function testCreateTaskWithRelations(): void
    {
        $column = new Column();
        $column->setTitle('In Progress');
        $status = new Status();
        $status->setName('Active');
        $user = new User();
        $user->setName('John')->setEmail('john@example.com');

        $this->columnRepository->method('findById')->with(1)->willReturn($column);
        $this->statusRepository->method('findById')->with(2)->willReturn($status);
        $this->taskRepository->expects($this->once())->method('save');

        $dto = CreateTaskRequest::fromArray([
            'title' => 'Task with relations',
            'sortOrder' => 1,
            'columnId' => 1,
            'statusId' => 2,
        ]);

        $task = $this->service->createTask($dto, $user);

        $this->assertSame($column, $task->getColumn());
        $this->assertSame($status, $task->getStatus());
        $this->assertSame($user, $task->getUser());
    }

    public function testUpdateTask(): void
    {
        $task = new Task();
        $task->setTitle('Old Title')->setSortOrder(0);

        $this->taskRepository->expects($this->once())->method('save');

        $dto = UpdateTaskRequest::fromArray(['title' => 'New Title']);
        $updated = $this->service->updateTask($task, $dto);

        $this->assertSame('New Title', $updated->getTitle());
    }

    public function testDeleteTask(): void
    {
        $task = new Task();
        $task->setTitle('To Delete')->setSortOrder(0);

        $this->taskRepository->expects($this->once())->method('remove')->with($task);

        $this->service->deleteTask($task);
    }

    public function testCreateTaskWithInvalidColumnIdThrowsValidationException(): void
    {
        $user = new User();
        $user->setName('John')->setEmail('john@example.com');

        $this->columnRepository->method('findById')->with(-1)->willReturn(null);

        $dto = CreateTaskRequest::fromArray([
            'title' => 'Task',
            'columnId' => -1,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Column id must be an integer');

        $this->service->createTask($dto, $user);
    }
}
