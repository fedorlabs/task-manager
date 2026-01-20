<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\Query\StatusQuery;
use App\Application\Dto\Status\CreateStatusRequest;
use App\Application\Dto\Status\UpdateStatusRequest;
use App\Application\Service\QueryParamResolver;
use App\Application\Service\RequestPayloadParser;
use App\Application\Service\RequestValidator;
use App\Application\Service\StatusService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class StatusController extends ApiController
{
    public function __construct(
        private readonly StatusService $statusService,
        private readonly RequestValidator $validator,
        private readonly RequestPayloadParser $payloadParser,
        private readonly QueryParamResolver $queryParams,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/statuses', name: 'api_statuses_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $query = StatusQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        return new JsonResponse($this->serializer->serialize($this->statusService->getStatuses($query), 'json', ['groups' => 'status:read']), json: true);
    }

    #[Route('/statuses/count', name: 'api_statuses_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->statusService->getStatusCount()]);
    }

    #[Route('/statuses/{id}', name: 'api_statuses_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->statusService->getStatusById($id), 'json', ['groups' => 'status:read']), json: true);
    }

    #[Route('/statuses', name: 'api_statuses_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = CreateStatusRequest::fromArray($data);
        $this->validator->validate($dto);
        $status = $this->statusService->createStatus($dto);
        return new JsonResponse($this->serializer->serialize($status, 'json', ['groups' => 'status:read']), json: true);
    }

    #[Route('/statuses/{id}', name: 'api_statuses_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = UpdateStatusRequest::fromArray($data);
        $this->validator->validate($dto);
        $status = $this->statusService->updateStatus($id, $dto);
        return new JsonResponse($this->serializer->serialize($status, 'json', ['groups' => 'status:read']), json: true);
    }

    #[Route('/statuses/{id}', name: 'api_statuses_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->statusService->deleteStatus($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
