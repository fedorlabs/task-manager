<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Column;
use App\Domain\Entity\Status;
use App\Domain\Entity\Task;
use App\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function testIdIsNullBeforePersist(): void
    {
        $task = new Task();

        $this->assertNull($task->getId());
    }

    public function testSetAndGetTitle(): void
    {
        $task = new Task();
        $task->setTitle('My Task');

        $this->assertSame('My Task', $task->getTitle());
    }

    public function testDescriptionDefaultNull(): void
    {
        $task = new Task();

        $this->assertNull($task->getDescription());
    }

    public function testSetDescription(): void
    {
        $task = new Task();
        $task->setDescription('A detailed description');

        $this->assertSame('A detailed description', $task->getDescription());
    }

    public function testSortOrderDefault(): void
    {
        $task = new Task();

        $this->assertSame(0, $task->getSortOrder());
    }

    public function testSetSortOrder(): void
    {
        $task = new Task();
        $task->setSortOrder(5);

        $this->assertSame(5, $task->getSortOrder());
    }

    public function testDueDateDefaultNull(): void
    {
        $task = new Task();

        $this->assertNull($task->getDueDate());
    }

    public function testSetDueDate(): void
    {
        $task = new Task();
        $date = new \DateTime('2026-12-31');
        $task->setDueDate($date);

        $this->assertSame($date, $task->getDueDate());
    }

    public function testSetNullDueDate(): void
    {
        $task = new Task();
        $task->setDueDate(new \DateTime());
        $task->setDueDate(null);

        $this->assertNull($task->getDueDate());
    }

    public function testCreatedAtSetOnConstruct(): void
    {
        $before = new \DateTime();
        $task = new Task();
        $after = new \DateTime();

        $this->assertGreaterThanOrEqual($before, $task->getCreatedAt());
        $this->assertLessThanOrEqual($after, $task->getCreatedAt());
    }

    public function testUpdatedAtSetOnConstruct(): void
    {
        $task = new Task();

        $this->assertInstanceOf(\DateTimeInterface::class, $task->getUpdatedAt());
    }

    public function testPreUpdateChangesUpdatedAt(): void
    {
        $task = new Task();
        $originalUpdatedAt = $task->getUpdatedAt();

        usleep(1000);
        $task->onPreUpdate();

        $this->assertGreaterThanOrEqual($originalUpdatedAt, $task->getUpdatedAt());
    }

    public function testSetAndGetColumn(): void
    {
        $task = new Task();
        $column = new Column();
        $column->setTitle('In Progress');

        $task->setColumn($column);

        $this->assertSame($column, $task->getColumn());
    }

    public function testSetNullColumn(): void
    {
        $task = new Task();
        $task->setColumn(new Column());
        $task->setColumn(null);

        $this->assertNull($task->getColumn());
    }

    public function testSetAndGetStatus(): void
    {
        $task = new Task();
        $status = new Status();
        $status->setName('Active');

        $task->setStatus($status);

        $this->assertSame($status, $task->getStatus());
    }

    public function testSetAndGetUser(): void
    {
        $task = new Task();
        $user = new User();
        $user->setName('John');

        $task->setUser($user);

        $this->assertSame($user, $task->getUser());
    }

    public function testSetAndGetTags(): void
    {
        $task = new Task();
        $task->setTags('Frontend#Backend#Design');

        $this->assertSame('Frontend#Backend#Design', $task->getTags());
    }

    public function testSetAndGetUrl(): void
    {
        $task = new Task();
        $task->setUrl('https://example.com');
        $task->setUrlDescription('Example');

        $this->assertSame('https://example.com', $task->getUrl());
        $this->assertSame('Example', $task->getUrlDescription());
    }

    public function testEmptyCollectionsOnCreate(): void
    {
        $task = new Task();

        $this->assertCount(0, $task->getComments());
        $this->assertCount(0, $task->getTicks());
    }

    public function testFluentInterface(): void
    {
        $task = new Task();
        $result = $task->setTitle('Test')
            ->setDescription('Desc')
            ->setSortOrder(1)
            ->setTags('tag1')
            ->setUrl('https://example.com')
            ->setUrlDescription('Example');

        $this->assertInstanceOf(Task::class, $result);
    }
}
