<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Column;
use PHPUnit\Framework\TestCase;

final class ColumnTest extends TestCase
{
    public function testIdIsNullBeforePersist(): void
    {
        $column = new Column();

        $this->assertNull($column->getId());
    }

    public function testSetAndGetTitle(): void
    {
        $column = new Column();
        $column->setTitle('In Progress');

        $this->assertSame('In Progress', $column->getTitle());
    }

    public function testEmptyTasksCollectionOnCreate(): void
    {
        $column = new Column();

        $this->assertCount(0, $column->getTasks());
    }

    public function testFluentSetTitle(): void
    {
        $column = new Column();
        $result = $column->setTitle('Done');

        $this->assertInstanceOf(Column::class, $result);
    }
}
