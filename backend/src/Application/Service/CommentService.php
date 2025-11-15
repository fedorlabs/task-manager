<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Comment;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentService
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /** @return Comment[] */
    public function getAllComments(): array
    {
        return $this->commentRepository->findAll();
    }

    public function getCommentById(int $id): Comment
    {
        $comment = $this->commentRepository->findById($id);
        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }
        return $comment;
    }

    /** @return Comment[] */
    public function getCommentsByTaskId(int $taskId): array
    {
        return $this->commentRepository->findByTaskId($taskId);
    }

    public function getCommentCount(): int
    {
        return $this->commentRepository->count();
    }

    public function createComment(array $data): Comment
    {
        $comment = new Comment();
        $this->hydrateComment($comment, $data);
        $this->commentRepository->save($comment);
        return $comment;
    }

    public function updateComment(int $id, array $data): Comment
    {
        $comment = $this->getCommentById($id);
        $this->hydrateComment($comment, $data);
        $this->commentRepository->save($comment);
        return $comment;
    }

    public function deleteComment(int $id): void
    {
        $comment = $this->getCommentById($id);
        $this->commentRepository->remove($comment);
    }

    private function hydrateComment(Comment $comment, array $data): void
    {
        if (array_key_exists('text', $data)) {
            $comment->setText($data['text']);
        }
        if (array_key_exists('taskId', $data)) {
            $comment->setTask($data['taskId'] ? $this->taskRepository->findById((int) $data['taskId']) : null);
        }
        if (array_key_exists('userId', $data)) {
            $comment->setUser($data['userId'] ? $this->userRepository->findById($data['userId']) : null);
        }
    }
}
