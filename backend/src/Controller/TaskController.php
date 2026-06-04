<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\Query\TaskQuery;
use App\Application\Dto\Task\CreateTaskRequest;
use App\Application\Dto\Task\UpdateTaskRequest;
use App\Application\Service\QueryParamResolver;
use App\Application\Service\RequestPayloadParser;
use App\Application\Service\RequestValidator;
use App\Application\Service\TaskService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Tasks', description: 'Управление задачами')]
class TaskController extends ApiController
{
    public function __construct(
        private readonly TaskService $taskService,
        private readonly RequestValidator $validator,
        private readonly RequestPayloadParser $payloadParser,
        private readonly QueryParamResolver $queryParams,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/tasks', name: 'api_tasks_list', methods: ['GET'])]
    #[OA\Get(
        path: '/tasks',
        summary: 'Список задач',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'columnId', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'statusId', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'q', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'offset', in: 'query', schema: new OA\Schema(type: 'integer', default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Список задач'),
            new OA\Response(response: 401, description: 'Не авторизован'),
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $query = TaskQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        $tasks = $this->taskService->getTasksForUser($user, $query);
        return new JsonResponse($this->serializer->serialize($tasks, 'json', ['groups' => 'task:read']), json: true);
    }

    #[Route('/tasks/count', name: 'api_tasks_count', methods: ['GET'])]
    #[OA\Get(
        path: '/tasks/count',
        summary: 'Количество задач',
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Количество', content: new OA\JsonContent(properties: [new OA\Property(property: 'count', type: 'integer')])),
        ]
    )]
    public function count(Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $query = TaskQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        return $this->json(['count' => $this->taskService->getTaskCountForUser($user, $query)]);
    }

    #[Route('/tasks/{id}', name: 'api_tasks_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        path: '/tasks/{id}',
        summary: 'Получить задачу по ID',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Задача'),
            new OA\Response(response: 404, description: 'Не найдена'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $task = $this->taskService->getTaskById($id);
        $this->denyAccessUnlessGranted('TASK_VIEW', $task);
        return new JsonResponse($this->serializer->serialize($task, 'json', ['groups' => 'task:read']), json: true);
    }

    #[Route('/tasks', name: 'api_tasks_create', methods: ['POST'])]
    #[OA\Post(
        path: '/tasks',
        summary: 'Создать задачу',
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'sortOrder', type: 'integer', nullable: true),
                    new OA\Property(property: 'dueDate', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'url', type: 'string', nullable: true),
                    new OA\Property(property: 'urlDescription', type: 'string', nullable: true),
                    new OA\Property(property: 'tags', type: 'string', nullable: true),
                    new OA\Property(property: 'columnId', type: 'integer', nullable: true),
                    new OA\Property(property: 'statusId', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Созданная задача'),
            new OA\Response(response: 400, description: 'Ошибка валидации'),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $data = $this->payloadParser->parse($request);
        $dto = CreateTaskRequest::fromArray($data);
        $this->validator->validate($dto);
        $task = $this->taskService->createTask($dto, $user);
        return new JsonResponse($this->serializer->serialize($task, 'json', ['groups' => 'task:read']), json: true);
    }

    #[Route('/tasks/{id}', name: 'api_tasks_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    #[OA\Patch(
        path: '/tasks/{id}',
        summary: 'Обновить задачу',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'sortOrder', type: 'integer', nullable: true),
                    new OA\Property(property: 'dueDate', type: 'string', format: 'date-time', nullable: true),
                    new OA\Property(property: 'url', type: 'string', nullable: true),
                    new OA\Property(property: 'urlDescription', type: 'string', nullable: true),
                    new OA\Property(property: 'tags', type: 'string', nullable: true),
                    new OA\Property(property: 'columnId', type: 'integer', nullable: true),
                    new OA\Property(property: 'statusId', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Обновлённая задача'),
            new OA\Response(response: 404, description: 'Не найдена'),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = UpdateTaskRequest::fromArray($data);
        $this->validator->validate($dto);
        $task = $this->taskService->getTaskById($id);
        $this->denyAccessUnlessGranted('TASK_EDIT', $task);
        $task = $this->taskService->updateTask($task, $dto);
        return new JsonResponse($this->serializer->serialize($task, 'json', ['groups' => 'task:read']), json: true);
    }

    #[Route('/tasks/{id}', name: 'api_tasks_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(
        path: '/tasks/{id}',
        summary: 'Удалить задачу',
        security: [['Bearer' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Удалена'),
            new OA\Response(response: 404, description: 'Не найдена'),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $task = $this->taskService->getTaskById($id);
        $this->denyAccessUnlessGranted('TASK_DELETE', $task);
        $this->taskService->deleteTask($task);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
