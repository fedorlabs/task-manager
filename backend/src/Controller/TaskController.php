<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\EntitySerializer;
use App\Application\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly EntitySerializer $serializer,
    ) {}

    #[Route('/tasks', name: 'api_tasks_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->serializer->serializeTasks($this->taskService->getAllTasks()));
    }

    #[Route('/tasks/count', name: 'api_tasks_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->taskService->getTaskCount()]);
    }

    #[Route('/tasks/{id}', name: 'api_tasks_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->json($this->serializer->serializeTask($this->taskService->getTaskById($id)));
    }

    #[Route('/tasks', name: 'api_tasks_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $task = $this->taskService->createTask($data);
        return $this->json($this->serializer->serializeTask($task));
    }

    #[Route('/tasks/{id}', name: 'api_tasks_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $task = $this->taskService->updateTask($id, $data);
        return $this->json($this->serializer->serializeTask($task));
    }

    #[Route('/tasks/{id}', name: 'api_tasks_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->taskService->deleteTask($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
