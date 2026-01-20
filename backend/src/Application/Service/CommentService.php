<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\Comment\CreateCommentRequest;
use App\Application\Dto\Comment\UpdateCommentRequest;
use App\Application\Dto\Query\CommentQuery;
use App\Domain\Entity\Comment;
use App\Domain\Entity\User;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;

class CommentService
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    /** @return Comment[] */
    public function getCommentsForUser(User $user, CommentQuery $query): array
    {
        if ($query->taskId !== null) {
            $task = $this->taskRepository->findById($query->taskId);
            if (!$task) {
                throw new NotFoundException('Task not found');
            }
        }

        $userFilter = $user->isAdmin() ? null : $user;
        return $this->commentRepository->findByFilters(
            $userFilter,
            $query->taskId,
            $query->q,
            $query->sort,
            $query->order ?? 'asc',
            $query->limit,
            $query->offset
        );
    }

    public function getCommentById(int $id): Comment
    {
        $comment = $this->commentRepository->findById($id);
        if (!$comment) {
            throw new NotFoundException('Comment not found');
        }
        return $comment;
    }

    /** @return Comment[] */
    public function getCommentsByTaskIdForUser(int $taskId, User $user, CommentQuery $query): array
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            throw new NotFoundException('Task not found');
        }
        $userFilter = $user->isAdmin() ? null : $user;
        return $this->commentRepository->findByFilters(
            $userFilter,
            $taskId,
            $query->q,
            $query->sort,
            $query->order ?? 'asc',
            $query->limit,
            $query->offset
        );
    }

    public function getCommentCountForUser(User $user, CommentQuery $query): int
    {
        if ($query->taskId !== null) {
            $task = $this->taskRepository->findById($query->taskId);
            if (!$task) {
                throw new NotFoundException('Task not found');
            }
        }

        $userFilter = $user->isAdmin() ? null : $user;
        return $this->commentRepository->countByFilters($userFilter, $query->taskId, $query->q);
    }

    public function createComment(CreateCommentRequest $dto, User $user): Comment
    {
        $comment = new Comment();
        $this->hydrateComment($comment, $dto, $user, true);
        $this->commentRepository->save($comment);
        return $comment;
    }

    public function updateComment(Comment $comment, UpdateCommentRequest $dto): Comment
    {
        $this->hydrateComment($comment, $dto, $comment->getUser(), false);
        $this->commentRepository->save($comment);
        return $comment;
    }

    public function deleteComment(Comment $comment): void
    {
        $this->commentRepository->remove($comment);
    }

    private function hydrateComment(
        Comment $comment,
        CreateCommentRequest|UpdateCommentRequest $dto,
        ?User $user,
        bool $isCreate
    ): void {
        if ($isCreate) {
            $comment->setText($dto->text);
        } elseif ($dto instanceof UpdateCommentRequest && $dto->text !== null) {
            $comment->setText($dto->text);
        }
        if ($dto instanceof CreateCommentRequest) {
            if ($dto->taskId === null) {
                throw new ValidationException('Task is required');
            }
            if ($dto->taskId === -1) {
                throw new ValidationException('Task id must be an integer');
            }
            $task = $this->taskRepository->findById($dto->taskId);
            if (!$task) {
                throw new NotFoundException('Task not found');
            }
            $comment->setTask($task);
        }
        if ($isCreate) {
            if (!$user) {
                throw new ValidationException('User is required');
            }
            $comment->setUser($user);
        }
    }
}
