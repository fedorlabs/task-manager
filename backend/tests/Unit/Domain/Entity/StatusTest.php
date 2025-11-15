<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\Domain\Entity\Status;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    private Status $status;

    protected function setUp(): void
    {
        $this->status = new Status();
    }

    public function testIdIsNullByDefault(): void
    {
        $this->assertNull($this->status->getId());
    }

    public function testSetAndGetName(): void
    {
        $result = $this->status->setName('Active');

        $this->assertSame('Active', $this->status->getName());
        $this->assertSame($this->status, $result, 'setName should return self for fluent interface');
    }

    public function testTasksCollectionIsInitialized(): void
    {
        $tasks = $this->status->getTasks();

        $this->assertInstanceOf(ArrayCollection::class, $tasks);
        $this->assertCount(0, $tasks);
    }

    public function testNameCanBeUpdated(): void
    {
        $this->status->setName('Draft');
        $this->assertSame('Draft', $this->status->getName());

        $this->status->setName('Published');
        $this->assertSame('Published', $this->status->getName());
    }
}
