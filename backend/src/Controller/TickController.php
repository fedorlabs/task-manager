<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\Query\TickQuery;
use App\Application\Dto\Tick\CreateTickRequest;
use App\Application\Dto\Tick\UpdateTickRequest;
use App\Application\Service\QueryParamResolver;
use App\Application\Service\RequestPayloadParser;
use App\Application\Service\RequestValidator;
use App\Application\Service\TickService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class TickController extends ApiController
{
    public function __construct(
        private readonly TickService $tickService,
        private readonly RequestValidator $validator,
        private readonly RequestPayloadParser $payloadParser,
        private readonly QueryParamResolver $queryParams,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/ticks', name: 'api_ticks_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $query = TickQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        $ticks = $this->tickService->getTicks($query);
        return new JsonResponse($this->serializer->serialize($ticks, 'json', ['groups' => 'tick:read']), json: true);
    }

    #[Route('/ticks/count', name: 'api_ticks_count', methods: ['GET'])]
    public function count(Request $request): JsonResponse
    {
        $query = TickQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        return $this->json(['count' => $this->tickService->getTickCount($query)]);
    }

    #[Route('/ticks/{id}', name: 'api_ticks_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->tickService->getTickById($id), 'json', ['groups' => 'tick:read']), json: true);
    }

    #[Route('/ticks', name: 'api_ticks_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = CreateTickRequest::fromArray($data);
        $this->validator->validate($dto);
        $tick = $this->tickService->createTick($dto);
        return new JsonResponse($this->serializer->serialize($tick, 'json', ['groups' => 'tick:read']), json: true);
    }

    #[Route('/ticks/{id}', name: 'api_ticks_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = UpdateTickRequest::fromArray($data);
        $this->validator->validate($dto);
        $tick = $this->tickService->updateTick($id, $dto);
        return new JsonResponse($this->serializer->serialize($tick, 'json', ['groups' => 'tick:read']), json: true);
    }

    #[Route('/ticks/{id}', name: 'api_ticks_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->tickService->deleteTick($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/tasks/{taskId}/ticks', name: 'api_task_ticks_list', methods: ['GET'], requirements: ['taskId' => '\d+'])]
    public function listByTask(int $taskId, Request $request): JsonResponse
    {
        $query = TickQuery::fromRequest($request, $this->queryParams);
        $query->taskId = $taskId;
        $this->validator->validate($query);
        return new JsonResponse($this->serializer->serialize($this->tickService->getTicksByTaskId($query), 'json', ['groups' => 'tick:read']), json: true);
    }

    #[Route('/tasks/{taskId}/ticks', name: 'api_task_ticks_create', methods: ['POST'], requirements: ['taskId' => '\d+'])]
    public function createForTask(int $taskId, Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $data['taskId'] = $taskId;
        $dto = CreateTickRequest::fromArray($data);
        $this->validator->validate($dto);
        $tick = $this->tickService->createTick($dto);
        return new JsonResponse($this->serializer->serialize($tick, 'json', ['groups' => 'tick:read']), json: true);
    }
}
