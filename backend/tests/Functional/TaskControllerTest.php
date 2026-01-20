<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private function createAuthenticatedClient(): array
    {
        $client = static::createClient();

        // Register and login
        $client->request('POST', '/signup', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Task Tester',
            'email' => 'task_tester@example.com',
            'password' => 'password123',
        ]));

        $client->request('POST', '/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'task_tester@example.com',
            'password' => 'password123',
        ]));

        $data = json_decode($client->getResponse()->getContent(), true);

        return [$client, $data];
    }

    public function testListTasksRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/tasks');

        $this->assertSame(401, $client->getResponse()->getStatusCode());
    }

    public function testCreateAndListTask(): void
    {
        [$client] = $this->createAuthenticatedClient();

        // Create task
        $client->request('POST', '/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'My Task',
            'description' => 'Task description',
        ]));

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $task = json_decode($response->getContent(), true);
        $this->assertSame('My Task', $task['title']);
        $this->assertSame('Task description', $task['description']);
        $this->assertArrayHasKey('id', $task);
        $this->assertArrayHasKey('user', $task);
        $this->assertSame('Task Tester', $task['user']['name']);

        // List tasks
        $client->request('GET', '/tasks');
        $list = json_decode($client->getResponse()->getContent(), true);
        $this->assertCount(1, $list);
        $this->assertSame('My Task', $list[0]['title']);
    }

    public function testUpdateTask(): void
    {
        [$client] = $this->createAuthenticatedClient();

        // Create task
        $client->request('POST', '/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'Old Title']));

        $task = json_decode($client->getResponse()->getContent(), true);
        $taskId = $task['id'];

        // Update
        $client->request('PATCH', "/tasks/{$taskId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'New Title']));

        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());

        $updated = json_decode($response->getContent(), true);
        $this->assertSame('New Title', $updated['title']);
    }

    public function testDeleteTask(): void
    {
        [$client] = $this->createAuthenticatedClient();

        // Create task
        $client->request('POST', '/tasks', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['title' => 'To Delete']));

        $task = json_decode($client->getResponse()->getContent(), true);
        $taskId = $task['id'];

        // Delete
        $client->request('DELETE', "/tasks/{$taskId}");
        $this->assertSame(204, $client->getResponse()->getStatusCode());

        // Verify deleted
        $client->request('GET', "/tasks/{$taskId}");
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testShowTaskNotFound(): void
    {
        [$client] = $this->createAuthenticatedClient();

        $client->request('GET', '/tasks/99999');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }
}
