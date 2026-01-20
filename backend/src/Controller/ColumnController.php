<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\Column\CreateColumnRequest;
use App\Application\Dto\Column\UpdateColumnRequest;
use App\Application\Dto\Query\ColumnQuery;
use App\Application\Service\ColumnService;
use App\Application\Service\QueryParamResolver;
use App\Application\Service\RequestPayloadParser;
use App\Application\Service\RequestValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ColumnController extends ApiController
{
    public function __construct(
        private readonly ColumnService $columnService,
        private readonly RequestValidator $validator,
        private readonly RequestPayloadParser $payloadParser,
        private readonly QueryParamResolver $queryParams,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/columns', name: 'api_columns_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $query = ColumnQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        return new JsonResponse($this->serializer->serialize($this->columnService->getColumns($query), 'json', ['groups' => 'column:read']), json: true);
    }

    #[Route('/columns/count', name: 'api_columns_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->columnService->getColumnCount()]);
    }

    #[Route('/columns/{id}', name: 'api_columns_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->columnService->getColumnById($id), 'json', ['groups' => 'column:read']), json: true);
    }

    #[Route('/columns', name: 'api_columns_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = CreateColumnRequest::fromArray($data);
        $this->validator->validate($dto);
        $column = $this->columnService->createColumn($dto);
        return new JsonResponse($this->serializer->serialize($column, 'json', ['groups' => 'column:read']), json: true);
    }

    #[Route('/columns/{id}', name: 'api_columns_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = UpdateColumnRequest::fromArray($data);
        $this->validator->validate($dto);
        $column = $this->columnService->updateColumn($id, $dto);
        return new JsonResponse($this->serializer->serialize($column, 'json', ['groups' => 'column:read']), json: true);
    }

    #[Route('/columns/{id}', name: 'api_columns_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->columnService->deleteColumn($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
