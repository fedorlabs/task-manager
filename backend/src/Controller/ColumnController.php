<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\ColumnService;
use App\Application\Service\EntitySerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ColumnController extends AbstractController
{
    public function __construct(
        private readonly ColumnService $columnService,
        private readonly EntitySerializer $serializer,
    ) {}

    #[Route('/columns', name: 'api_columns_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->serializer->serializeColumns($this->columnService->getAllColumns()));
    }

    #[Route('/columns/count', name: 'api_columns_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->columnService->getColumnCount()]);
    }

    #[Route('/columns/{id}', name: 'api_columns_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->json($this->serializer->serializeColumn($this->columnService->getColumnById($id)));
    }

    #[Route('/columns', name: 'api_columns_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $column = $this->columnService->createColumn($data['title'] ?? '');
        return $this->json($this->serializer->serializeColumn($column));
    }

    #[Route('/columns/{id}', name: 'api_columns_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $column = $this->columnService->updateColumn($id, $data);
        return $this->json($this->serializer->serializeColumn($column));
    }

    #[Route('/columns/{id}', name: 'api_columns_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->columnService->deleteColumn($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
