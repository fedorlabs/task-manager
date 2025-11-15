<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Column;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Status;
use App\Domain\Entity\Task;
use App\Domain\Entity\Tick;
use App\Domain\Entity\User;

class EntitySerializer
{
    public function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'isAdmin' => $user->isAdmin(),
            'avatar' => $user->getAvatar(),
        ];
    }

    /** @param User[] $users */
    public function serializeUsers(array $users): array
    {
        return array_map(fn(User $u) => $this->serializeUser($u), $users);
    }

    public function serializeTask(Task $task, bool $includeRelations = true): array
    {
        $data = [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'sortOrder' => $task->getSortOrder(),
            'dueDate' => $task->getDueDate()?->format('c'),
            'url' => $task->getUrl(),
            'urlDescription' => $task->getUrlDescription(),
            'tags' => $task->getTags(),
            'createdAt' => $task->getCreatedAt()->format('c'),
            'updatedAt' => $task->getUpdatedAt()->format('c'),
            'columnId' => $task->getColumn()?->getId(),
            'statusId' => $task->getStatus()?->getId(),
            'userId' => $task->getUser()?->getId(),
        ];

        if ($includeRelations) {
            $data['user'] = $task->getUser() ? $this->serializeUser($task->getUser()) : null;
        }

        return $data;
    }

    /** @param Task[] $tasks */
    public function serializeTasks(array $tasks): array
    {
        return array_map(fn(Task $t) => $this->serializeTask($t), $tasks);
    }

    public function serializeColumn(Column $column): array
    {
        return [
            'id' => $column->getId(),
            'title' => $column->getTitle(),
        ];
    }

    /** @param Column[] $columns */
    public function serializeColumns(array $columns): array
    {
        return array_map(fn(Column $c) => $this->serializeColumn($c), $columns);
    }

    public function serializeStatus(Status $status): array
    {
        return [
            'id' => $status->getId(),
            'name' => $status->getName(),
        ];
    }

    /** @param Status[] $statuses */
    public function serializeStatuses(array $statuses): array
    {
        return array_map(fn(Status $s) => $this->serializeStatus($s), $statuses);
    }

    public function serializeComment(Comment $comment): array
    {
        return [
            'id' => $comment->getId(),
            'text' => $comment->getText(),
            'createdAt' => $comment->getCreatedAt()->format('c'),
            'updatedAt' => $comment->getUpdatedAt()->format('c'),
            'taskId' => $comment->getTask()?->getId(),
            'userId' => $comment->getUser()?->getId(),
        ];
    }

    /** @param Comment[] $comments */
    public function serializeComments(array $comments): array
    {
        return array_map(fn(Comment $c) => $this->serializeComment($c), $comments);
    }

    public function serializeTick(Tick $tick): array
    {
        return [
            'id' => $tick->getId(),
            'text' => $tick->getText(),
            'done' => $tick->isDone(),
            'taskId' => $tick->getTask()?->getId(),
        ];
    }

    /** @param Tick[] $ticks */
    public function serializeTicks(array $ticks): array
    {
        return array_map(fn(Tick $t) => $this->serializeTick($t), $ticks);
    }
}
