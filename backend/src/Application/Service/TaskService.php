<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Task;
use App\Domain\Repository\ColumnRepositoryInterface;
use App\Domain\Repository\StatusRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly ColumnRepositoryInterface $columnRepository,
        private readonly StatusRepositoryInterface $statusRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /** @return Task[] */
    public function getAllTasks(): array
    {
        return $this->taskRepository->findAll();
    }

    public function getTaskById(int $id): Task
    {
        $task = $this->taskRepository->findById($id);
        if (!$task) {
            throw new NotFoundHttpException('Task not found');
        }
        return $task;
    }

    public function getTaskCount(): int
    {
        return $this->taskRepository->count();
    }

    public function createTask(array $data): Task
    {
        $task = new Task();
        $this->hydrateTask($task, $data);
        $this->taskRepository->save($task);
        return $task;
    }

    public function updateTask(int $id, array $data): Task
    {
        $task = $this->getTaskById($id);
        $this->hydrateTask($task, $data);
        $this->taskRepository->save($task);
        return $task;
    }

    public function deleteTask(int $id): void
    {
        $task = $this->getTaskById($id);
        $this->taskRepository->remove($task);
    }

    private function hydrateTask(Task $task, array $data): void
    {
        if (array_key_exists('title', $data)) {
            $task->setTitle($data['title']);
        }
        if (array_key_exists('description', $data)) {
            $task->setDescription($data['description']);
        }
        if (array_key_exists('sortOrder', $data)) {
            $task->setSortOrder((int) $data['sortOrder']);
        }
        if (array_key_exists('dueDate', $data)) {
            $task->setDueDate($data['dueDate'] ? new \DateTime($data['dueDate']) : null);
        }
        if (array_key_exists('url', $data)) {
            $task->setUrl($data['url']);
        }
        if (array_key_exists('urlDescription', $data)) {
            $task->setUrlDescription($data['urlDescription']);
        }
        if (array_key_exists('tags', $data)) {
            $task->setTags($data['tags']);
        }
        if (array_key_exists('columnId', $data)) {
            $task->setColumn($data['columnId'] ? $this->columnRepository->findById((int) $data['columnId']) : null);
        }
        if (array_key_exists('statusId', $data)) {
            $task->setStatus($data['statusId'] ? $this->statusRepository->findById((int) $data['statusId']) : null);
        }
        if (array_key_exists('userId', $data)) {
            $task->setUser($data['userId'] ? $this->userRepository->findById($data['userId']) : null);
        }
    }
}
