<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Comment;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    public function testIdIsNullBeforePersist(): void
    {
        $comment = new Comment();

        $this->assertNull($comment->getId());
    }

    public function testSetAndGetText(): void
    {
        $comment = new Comment();
        $comment->setText('This is a comment');

        $this->assertSame('This is a comment', $comment->getText());
    }

    public function testCreatedAtSetOnConstruct(): void
    {
        $comment = new Comment();

        $this->assertInstanceOf(\DateTimeInterface::class, $comment->getCreatedAt());
    }

    public function testPreUpdateChangesUpdatedAt(): void
    {
        $comment = new Comment();
        $original = $comment->getUpdatedAt();

        usleep(1000);
        $comment->onPreUpdate();

        $this->assertGreaterThanOrEqual($original, $comment->getUpdatedAt());
    }

    public function testSetAndGetTask(): void
    {
        $comment = new Comment();
        $task = new Task();
        $task->setTitle('Test Task');

        $comment->setTask($task);

        $this->assertSame($task, $comment->getTask());
    }

    public function testSetAndGetUser(): void
    {
        $comment = new Comment();
        $user = new User();
        $user->setName('John');

        $comment->setUser($user);

        $this->assertSame($user, $comment->getUser());
    }

    public function testTaskAndUserDefaultNull(): void
    {
        $comment = new Comment();

        $this->assertNull($comment->getTask());
        $this->assertNull($comment->getUser());
    }
}
