<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\EntitySerializer;
use App\Domain\Entity\Column;
use App\Domain\Entity\Comment;
use App\Domain\Entity\Status;
use App\Domain\Entity\Task;
use App\Domain\Entity\Tick;
use App\Domain\Entity\User;
use PHPUnit\Framework\TestCase;

final class EntitySerializerTest extends TestCase
{
    private EntitySerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new EntitySerializer();
    }

    public function testSerializeUser(): void
    {
        $user = new User();
        $user->setName('John')
            ->setEmail('john@example.com')
            ->setIsAdmin(true)
            ->setAvatar('/avatar.jpg')
            ->setPassword('hashed');

        $data = $this->serializer->serializeUser($user);

        $this->assertSame('John', $data['name']);
        $this->assertSame('john@example.com', $data['email']);
        $this->assertTrue($data['isAdmin']);
        $this->assertSame('/avatar.jpg', $data['avatar']);
        $this->assertArrayNotHasKey('password', $data);
    }

    public function testSerializeUsers(): void
    {
        $user1 = new User();
        $user1->setName('A')->setEmail('a@test.com')->setPassword('x');
        $user2 = new User();
        $user2->setName('B')->setEmail('b@test.com')->setPassword('x');

        $data = $this->serializer->serializeUsers([$user1, $user2]);

        $this->assertCount(2, $data);
        $this->assertSame('A', $data[0]['name']);
        $this->assertSame('B', $data[1]['name']);
    }

    public function testSerializeTask(): void
    {
        $task = new Task();
        $task->setTitle('My Task')
            ->setDescription('Desc')
            ->setSortOrder(3)
            ->setTags('Frontend#Backend')
            ->setUrl('https://example.com')
            ->setUrlDescription('Link');

        $data = $this->serializer->serializeTask($task);

        $this->assertSame('My Task', $data['title']);
        $this->assertSame('Desc', $data['description']);
        $this->assertSame(3, $data['sortOrder']);
        $this->assertSame('Frontend#Backend', $data['tags']);
        $this->assertNull($data['columnId']);
        $this->assertNull($data['statusId']);
        $this->assertNull($data['userId']);
        $this->assertNull($data['user']);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }

    public function testSerializeTaskWithRelations(): void
    {
        $user = new User();
        $user->setName('John')->setEmail('john@test.com')->setPassword('x');

        $task = new Task();
        $task->setTitle('Task')
            ->setSortOrder(0)
            ->setUser($user);

        $data = $this->serializer->serializeTask($task);

        $this->assertIsArray($data['user']);
        $this->assertSame('John', $data['user']['name']);
    }

    public function testSerializeColumn(): void
    {
        $column = new Column();
        $column->setTitle('Done');

        $data = $this->serializer->serializeColumn($column);

        $this->assertNull($data['id']);
        $this->assertSame('Done', $data['title']);
    }

    public function testSerializeStatus(): void
    {
        $status = new Status();
        $status->setName('Active');

        $data = $this->serializer->serializeStatus($status);

        $this->assertSame('Active', $data['name']);
    }

    public function testSerializeComment(): void
    {
        $comment = new Comment();
        $comment->setText('Hello world');

        $data = $this->serializer->serializeComment($comment);

        $this->assertSame('Hello world', $data['text']);
        $this->assertNull($data['taskId']);
        $this->assertNull($data['userId']);
        $this->assertArrayHasKey('createdAt', $data);
    }

    public function testSerializeTick(): void
    {
        $tick = new Tick();
        $tick->setText('Subtask 1')
            ->setDone(true);

        $data = $this->serializer->serializeTick($tick);

        $this->assertSame('Subtask 1', $data['text']);
        $this->assertTrue($data['done']);
        $this->assertNull($data['taskId']);
    }

    public function testSerializeTicksArray(): void
    {
        $tick1 = new Tick();
        $tick1->setText('A')->setDone(false);
        $tick2 = new Tick();
        $tick2->setText('B')->setDone(true);

        $data = $this->serializer->serializeTicks([$tick1, $tick2]);

        $this->assertCount(2, $data);
        $this->assertSame('A', $data[0]['text']);
        $this->assertTrue($data[1]['done']);
    }
}
