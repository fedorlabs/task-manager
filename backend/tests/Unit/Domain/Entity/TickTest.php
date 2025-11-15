<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Task;
use App\Domain\Entity\Tick;
use PHPUnit\Framework\TestCase;

final class TickTest extends TestCase
{
    public function testIdIsNullBeforePersist(): void
    {
        $tick = new Tick();

        $this->assertNull($tick->getId());
    }

    public function testSetAndGetText(): void
    {
        $tick = new Tick();
        $tick->setText('Subtask item');

        $this->assertSame('Subtask item', $tick->getText());
    }

    public function testSetAndGetDone(): void
    {
        $tick = new Tick();
        $tick->setDone(false);

        $this->assertFalse($tick->isDone());

        $tick->setDone(true);

        $this->assertTrue($tick->isDone());
    }

    public function testSetAndGetTask(): void
    {
        $tick = new Tick();
        $task = new Task();
        $task->setTitle('Parent Task');

        $tick->setTask($task);

        $this->assertSame($task, $tick->getTask());
    }

    public function testTaskDefaultNull(): void
    {
        $tick = new Tick();

        $this->assertNull($tick->getTask());
    }

    public function testFluentInterface(): void
    {
        $tick = new Tick();
        $result = $tick->setText('Test')
            ->setDone(true)
            ->setTask(null);

        $this->assertInstanceOf(Tick::class, $result);
    }
}
