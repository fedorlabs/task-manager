<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\CommentService;
use App\Application\Service\EntitySerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CommentController extends AbstractController
{
    public function __construct(
        private readonly CommentService $commentService,
        private readonly EntitySerializer $serializer,
    ) {}

    #[Route('/comments', name: 'api_comments_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->serializer->serializeComments($this->commentService->getAllComments()));
    }

    #[Route('/comments/count', name: 'api_comments_count', methods: ['GET'])]
    public function count(): JsonResponse
    {
        return $this->json(['count' => $this->commentService->getCommentCount()]);
    }

    #[Route('/comments/{id}', name: 'api_comments_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return $this->json($this->serializer->serializeComment($this->commentService->getCommentById($id)));
    }

    #[Route('/comments', name: 'api_comments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $comment = $this->commentService->createComment($data);
        return $this->json($this->serializer->serializeComment($comment));
    }

    #[Route('/comments/{id}', name: 'api_comments_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $comment = $this->commentService->updateComment($id, $data);
        return $this->json($this->serializer->serializeComment($comment));
    }

    #[Route('/comments/{id}', name: 'api_comments_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $this->commentService->deleteComment($id);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/tasks/{taskId}/comments', name: 'api_task_comments_list', methods: ['GET'], requirements: ['taskId' => '\d+'])]
    public function listByTask(int $taskId): JsonResponse
    {
        return $this->json($this->serializer->serializeComments($this->commentService->getCommentsByTaskId($taskId)));
    }

    #[Route('/tasks/{taskId}/comments', name: 'api_task_comments_create', methods: ['POST'], requirements: ['taskId' => '\d+'])]
    public function createForTask(int $taskId, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $data['taskId'] = $taskId;
        $comment = $this->commentService->createComment($data);
        return $this->json($this->serializer->serializeComment($comment));
    }
}
