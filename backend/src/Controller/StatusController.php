<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\EntitySerializer;
use App\Application\Service\StatusService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatusController extends AbstractController
{
    public function __construct(
        private readonly StatusService $statusService,
        private readonly EntitySerializer $serializer,
    ) {}

    #[Route('/statuses', name: 'api_statuses_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->serializer->serializeStatuses($this->statusService->getAllStatuses()));
    }

    #[Route('/statuses/count', name: 'api_statuses_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->statusService->getStatusCount()]);
    }

    #[Route('/statuses/{id}', name: 'api_statuses_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->json($this->serializer->serializeStatus($this->statusService->getStatusById($id)));
    }

    #[Route('/statuses', name: 'api_statuses_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $status = $this->statusService->createStatus($data['name'] ?? '');
        return $this->json($this->serializer->serializeStatus($status));
    }

    #[Route('/statuses/{id}', name: 'api_statuses_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $status = $this->statusService->updateStatus($id, $data);
        return $this->json($this->serializer->serializeStatus($status));
    }

    #[Route('/statuses/{id}', name: 'api_statuses_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->statusService->deleteStatus($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
