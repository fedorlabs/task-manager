<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Dto\Comment\CreateCommentRequest;
use App\Application\Dto\Comment\UpdateCommentRequest;
use App\Application\Dto\Query\CommentQuery;
use App\Application\Service\CommentService;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CommentServiceTest extends TestCase
{
    private CommentRepositoryInterface&MockObject $commentRepository;
    private TaskRepositoryInterface&MockObject $taskRepository;
    private CommentService $service;

    protected function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);

        $this->service = new CommentService(
            $this->commentRepository,
            $this->taskRepository,
        );
    }

    public function testGetCommentByIdReturnsComment(): void
    {
        $comment = new Comment();
        $comment->setText('Hello');

        $this->commentRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($comment);

        $result = $this->service->getCommentById(1);
        $this->assertSame('Hello', $result->getText());
    }

    public function testGetCommentByIdThrowsNotFound(): void
    {
        $this->commentRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->getCommentById(999);
    }

    public function testGetCommentsForUser(): void
    {
        $user = new User();
        $user->setName('User')->setEmail('u@example.com');

        $comment = new Comment();
        $comment->setText('Text');

        $this->commentRepository->expects($this->once())
            ->method('findByFilters')
            ->willReturn([$comment]);

        $query = new CommentQuery();
        $comments = $this->service->getCommentsForUser($user, $query);
        $this->assertCount(1, $comments);
    }

    public function testGetCommentsForUserWithInvalidTaskThrowsNotFound(): void
    {
        $user = new User();
        $user->setName('User')->setEmail('u@example.com');

        $query = new CommentQuery();
        $query->taskId = 999;

        $this->taskRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->getCommentsForUser($user, $query);
    }

    public function testCreateComment(): void
    {
        $user = new User();
        $user->setName('User')->setEmail('u@example.com');

        $task = new Task();
        $task->setTitle('Task')->setSortOrder(0);

        $this->taskRepository->method('findById')->with(5)->willReturn($task);
        $this->commentRepository->expects($this->once())->method('save');

        $dto = CreateCommentRequest::fromArray([
            'text' => 'New comment',
            'taskId' => 5,
        ]);

        $comment = $this->service->createComment($dto, $user);
        $this->assertSame('New comment', $comment->getText());
        $this->assertSame($task, $comment->getTask());
    }

    public function testCreateCommentWithMissingTaskThrowsValidationException(): void
    {
        $user = new User();
        $user->setName('User')->setEmail('u@example.com');

        $dto = CreateCommentRequest::fromArray([
            'text' => 'New comment',
            'taskId' => null,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Task is required');

        $this->service->createComment($dto, $user);
    }

    public function testCreateCommentWithInvalidTaskIdThrowsValidationException(): void
    {
        $user = new User();
        $user->setName('User')->setEmail('u@example.com');

        $dto = CreateCommentRequest::fromArray([
            'text' => 'New comment',
            'taskId' => -1,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Task id must be an integer');

        $this->service->createComment($dto, $user);
    }

    public function testUpdateComment(): void
    {
        $comment = new Comment();
        $comment->setText('Old');

        $this->commentRepository->expects($this->once())->method('save');

        $dto = UpdateCommentRequest::fromArray(['text' => 'New']);
        $updated = $this->service->updateComment($comment, $dto);

        $this->assertSame('New', $updated->getText());
    }

    public function testDeleteComment(): void
    {
        $comment = new Comment();
        $comment->setText('To Delete');

        $this->commentRepository->expects($this->once())->method('remove')->with($comment);

        $this->service->deleteComment($comment);
    }
}
