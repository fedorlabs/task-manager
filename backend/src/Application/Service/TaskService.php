<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\Query\TaskQuery;
use App\Application\Dto\Task\CreateTaskRequest;
use App\Application\Dto\Task\UpdateTaskRequest;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\ColumnRepositoryInterface;
use App\Domain\Repository\StatusRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ColumnRepositoryInterface $columnRepository,
        private readonly StatusRepositoryInterface $statusRepository,
    ) {}

    /** @return Task[] */
    public function getTasksForUser(User $user, TaskQuery $query): array
    {
        if ($query->columnId !== null && !$this->columnRepository->findById($query->columnId)) {
            throw new ValidationException('Column not found');
        }
        if ($query->statusId !== null && !$this->statusRepository->findById($query->statusId)) {
            throw new ValidationException('Status not found');
        }

        $userFilter = $user->isAdmin() ? null : $user;
        return $this->taskRepository->findByFilters(
            $userFilter,
            $query->columnId,
            $query->statusId,
            $query->q,
            $query->sort,
            $query->order ?? 'asc',
            $query->limit,
            $query->offset
        );
    }

    public function getTaskById(int $id): Task
    {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new NotFoundException('Task not found');
        }
        return $task;
    }

    public function getTaskCountForUser(User $user, TaskQuery $query): int
    {
        if ($query->columnId !== null && !$this->columnRepository->findById($query->columnId)) {
            throw new ValidationException('Column not found');
        }
        if ($query->statusId !== null && !$this->statusRepository->findById($query->statusId)) {
            throw new ValidationException('Status not found');
        }

        $userFilter = $user->isAdmin() ? null : $user;
        return $this->taskRepository->countByFilters(
            $userFilter,
            $query->columnId,
            $query->statusId,
            $query->q
        );
    }

    public function createTask(CreateTaskRequest $dto, User $user): Task
    {
        $task = new Task();
        $this->hydrateForCreate($task, $dto, $user);
        $this->taskRepository->save($task);
        return $task;
    }

    public function updateTask(Task $task, UpdateTaskRequest $dto): Task
    {
        $this->hydrateForUpdate($task, $dto);
        $this->taskRepository->save($task);
        return $task;
    }

    public function deleteTask(Task $task): void
    {
        $this->taskRepository->remove($task);
    }

    private function hydrateForCreate(Task $task, CreateTaskRequest $dto, User $user): void
    {
        $task->setTitle($dto->title);
        $task->setUser($user);

        if ($dto->description !== null) {
            $task->setDescription($dto->description);
        }
        if ($dto->sortOrder !== null) {
            $task->setSortOrder($dto->sortOrder);
        }
        if ($dto->dueDate !== null) {
            $task->setDueDate($this->parseDateOrFail($dto->dueDate));
        }
        if ($dto->url !== null) {
            $task->setUrl($dto->url);
        }
        if ($dto->urlDescription !== null) {
            $task->setUrlDescription($dto->urlDescription);
        }
        if ($dto->tags !== null) {
            $task->setTags($dto->tags);
        }
        if ($dto->columnId !== null) {
            $this->setColumnOnTask($task, $dto->columnId);
        }
        if ($dto->statusId !== null) {
            $this->setStatusOnTask($task, $dto->statusId);
        }
    }

    private function hydrateForUpdate(Task $task, UpdateTaskRequest $dto): void
    {
        if ($dto->isProvided(UpdateTaskRequest::FIELD_TITLE)) {
            if ($dto->title === null) {
                throw new ValidationException('Title is required');
            }
            $task->setTitle($dto->title);
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_DESCRIPTION)) {
            $task->setDescription($dto->description);
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_SORT_ORDER) && $dto->sortOrder !== null) {
            $task->setSortOrder($dto->sortOrder);
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_DUE_DATE)) {
            $task->setDueDate($dto->dueDate !== null ? $this->parseDateOrFail($dto->dueDate) : null);
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_URL)) {
            $task->setUrl($dto->url);
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_URL_DESCRIPTION)) {
            $task->setUrlDescription($dto->urlDescription);
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_TAGS)) {
            $task->setTags($dto->tags);
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_COLUMN_ID)) {
            if ($dto->columnId === null) {
                $task->setColumn(null);
            } else {
                $this->setColumnOnTask($task, $dto->columnId);
            }
        }
        if ($dto->isProvided(UpdateTaskRequest::FIELD_STATUS_ID)) {
            if ($dto->statusId === null) {
                $task->setStatus(null);
            } else {
                $this->setStatusOnTask($task, $dto->statusId);
            }
        }
    }

    private function setColumnOnTask(Task $task, int $columnId): void
    {
        if ($columnId === -1) {
            throw new ValidationException('Column id must be an integer');
        }
        $column = $this->columnRepository->findById($columnId);
        if (!$column) {
            throw new ValidationException('Column not found');
        }
        $task->setColumn($column);
    }

    private function setStatusOnTask(Task $task, int $statusId): void
    {
        if ($statusId === -1) {
            throw new ValidationException('Status id must be an integer');
        }
        $status = $this->statusRepository->findById($statusId);
        if (!$status) {
            throw new ValidationException('Status not found');
        }
        $task->setStatus($status);
    }

    private function parseDateOrFail(string $value): ?\DateTimeInterface
    {
        try {
            return new \DateTime($value);
        } catch (\Exception) {
            throw new ValidationException('Invalid dueDate format');
        }
    }
}
