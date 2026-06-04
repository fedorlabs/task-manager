<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\CommentService;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use App\Domain\Repository\CommentRepositoryInterface;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CommentServiceTest extends TestCase
{
    private CommentRepositoryInterface&MockObject $commentRepository;
    private TaskRepositoryInterface&MockObject $taskRepository;
    private UserRepositoryInterface&MockObject $userRepository;
    private CommentService $service;

    protected function setUp(): void
    {
        $this->commentRepository = $this->createMock(CommentRepositoryInterface::class);
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);

        $this->service = new CommentService(
            $this->commentRepository,
            $this->taskRepository,
            $this->userRepository,
        );
    }

    public function testGetAllComments(): void
    {
        $c1 = new Comment();
        $c1->setText('First');
        $c2 = new Comment();
        $c2->setText('Second');

        $this->commentRepository->expects($this->once())
            ->method('findAll')
            ->willReturn([$c1, $c2]);

        $result = $this->service->getAllComments();

        $this->assertCount(2, $result);
        $this->assertSame('First', $result[0]->getText());
        $this->assertSame('Second', $result[1]->getText());
    }

    public function testGetCommentByIdReturnsComment(): void
    {
        $comment = new Comment();
        $comment->setText('Found');

        $this->commentRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($comment);

        $result = $this->service->getCommentById(1);

        $this->assertSame('Found', $result->getText());
    }

    public function testGetCommentByIdThrowsNotFound(): void
    {
        $this->commentRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Comment not found');

        $this->service->getCommentById(999);
    }

    public function testGetCommentsByTaskId(): void
    {
        $c1 = new Comment();
        $c1->setText('Task comment');

        $this->commentRepository->expects($this->once())
            ->method('findByTaskId')
            ->with(5)
            ->willReturn([$c1]);

        $result = $this->service->getCommentsByTaskId(5);

        $this->assertCount(1, $result);
        $this->assertSame('Task comment', $result[0]->getText());
    }

    public function testGetCommentCount(): void
    {
        $this->commentRepository->expects($this->once())
            ->method('count')
            ->willReturn(15);

        $this->assertSame(15, $this->service->getCommentCount());
    }

    public function testCreateComment(): void
    {
        $this->commentRepository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Comment::class));

        $comment = $this->service->createComment(['text' => 'New comment']);

        $this->assertSame('New comment', $comment->getText());
    }

    public function testCreateCommentWithRelations(): void
    {
        $task = new Task();
        $task->setTitle('Related task')->setSortOrder(0);
        $user = new User();
        $user->setName('Author');

        $this->taskRepository->method('findById')->with(3)->willReturn($task);
        $this->userRepository->method('findById')->with('uuid-42')->willReturn($user);
        $this->commentRepository->expects($this->once())->method('save');

        $comment = $this->service->createComment([
            'text' => 'With relations',
            'taskId' => 3,
            'userId' => 'uuid-42',
        ]);

        $this->assertSame($task, $comment->getTask());
        $this->assertSame($user, $comment->getUser());
    }

    public function testCreateCommentWithNullRelations(): void
    {
        $this->commentRepository->expects($this->once())->method('save');

        $comment = $this->service->createComment([
            'text' => 'Orphan',
            'taskId' => null,
            'userId' => null,
        ]);

        $this->assertNull($comment->getTask());
        $this->assertNull($comment->getUser());
    }

    public function testUpdateComment(): void
    {
        $comment = new Comment();
        $comment->setText('Old text');

        $this->commentRepository->method('findById')->with(1)->willReturn($comment);
        $this->commentRepository->expects($this->once())->method('save');

        $updated = $this->service->updateComment(1, ['text' => 'Updated text']);

        $this->assertSame('Updated text', $updated->getText());
    }

    public function testDeleteComment(): void
    {
        $comment = new Comment();
        $comment->setText('To delete');

        $this->commentRepository->method('findById')->with(1)->willReturn($comment);
        $this->commentRepository->expects($this->once())
            ->method('remove')
            ->with($comment);

        $this->service->deleteComment(1);
    }

    public function testDeleteCommentNotFoundThrows(): void
    {
        $this->commentRepository->method('findById')->with(999)->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->service->deleteComment(999);
    }
}
