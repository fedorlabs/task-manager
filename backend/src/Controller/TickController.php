<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\EntitySerializer;
use App\Application\Service\TickService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TickController extends AbstractController
{
    public function __construct(
        private readonly TickService $tickService,
        private readonly EntitySerializer $serializer,
    ) {}

    #[Route('/ticks', name: 'api_ticks_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->serializer->serializeTicks($this->tickService->getAllTicks()));
    }

    #[Route('/ticks/count', name: 'api_ticks_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->tickService->getTickCount()]);
    }

    #[Route('/ticks/{id}', name: 'api_ticks_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->json($this->serializer->serializeTick($this->tickService->getTickById($id)));
    }

    #[Route('/ticks', name: 'api_ticks_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $tick = $this->tickService->createTick($data);
        return $this->json($this->serializer->serializeTick($tick));
    }

    #[Route('/ticks/{id}', name: 'api_ticks_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $tick = $this->tickService->updateTick($id, $data);
        return $this->json($this->serializer->serializeTick($tick));
    }

    #[Route('/ticks/{id}', name: 'api_ticks_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->tickService->deleteTick($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/tasks/{taskId}/ticks', name: 'api_task_ticks_list', methods: ['GET'], requirements: ['taskId' => '\d+'])]
    public function listByTask(int $taskId): JsonResponse
    {
        return $this->json($this->serializer->serializeTicks($this->tickService->getTicksByTaskId($taskId)));
    }

    #[Route('/tasks/{taskId}/ticks', name: 'api_task_ticks_create', methods: ['POST'], requirements: ['taskId' => '\d+'])]
    public function createForTask(int $taskId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $data['taskId'] = $taskId;
        $tick = $this->tickService->createTick($data);
        return $this->json($this->serializer->serializeTick($tick));
    }
}
