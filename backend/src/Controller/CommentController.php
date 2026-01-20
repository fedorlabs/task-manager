<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\Comment\CreateCommentRequest;
use App\Application\Dto\Comment\UpdateCommentRequest;
use App\Application\Dto\Query\CommentQuery;
use App\Application\Service\CommentService;
use App\Application\Service\QueryParamResolver;
use App\Application\Service\RequestPayloadParser;
use App\Application\Service\RequestValidator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CommentController extends ApiController
{
    public function __construct(
        private readonly CommentService $commentService,
        private readonly RequestValidator $validator,
        private readonly RequestPayloadParser $payloadParser,
        private readonly QueryParamResolver $queryParams,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/comments', name: 'api_comments_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $query = CommentQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        $comments = $this->commentService->getCommentsForUser($user, $query);
        return new JsonResponse($this->serializer->serialize($comments, 'json', ['groups' => 'comment:read']), json: true);
    }

    #[Route('/comments/count', name: 'api_comments_count', methods: ['GET'])]
    public function count(Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $query = CommentQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);
        return $this->json(['count' => $this->commentService->getCommentCountForUser($user, $query)]);
    }

    #[Route('/comments/{id}', name: 'api_comments_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $comment = $this->commentService->getCommentById($id);
        $this->denyAccessUnlessGranted('COMMENT_VIEW', $comment);
        return new JsonResponse($this->serializer->serialize($comment, 'json', ['groups' => 'comment:read']), json: true);
    }

    #[Route('/comments', name: 'api_comments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $data = $this->payloadParser->parse($request);
        $dto = CreateCommentRequest::fromArray($data);
        $this->validator->validate($dto);
        $comment = $this->commentService->createComment($dto, $user);
        return new JsonResponse($this->serializer->serialize($comment, 'json', ['groups' => 'comment:read']), json: true);
    }

    #[Route('/comments/{id}', name: 'api_comments_update', methods: ['PATCH', 'PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = UpdateCommentRequest::fromArray($data);
        $this->validator->validate($dto);
        $comment = $this->commentService->getCommentById($id);
        $this->denyAccessUnlessGranted('COMMENT_EDIT', $comment);
        $comment = $this->commentService->updateComment($comment, $dto);
        return new JsonResponse($this->serializer->serialize($comment, 'json', ['groups' => 'comment:read']), json: true);
    }

    #[Route('/comments/{id}', name: 'api_comments_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $comment = $this->commentService->getCommentById($id);
        $this->denyAccessUnlessGranted('COMMENT_DELETE', $comment);
        $this->commentService->deleteComment($comment);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/tasks/{taskId}/comments', name: 'api_task_comments_list', methods: ['GET'], requirements: ['taskId' => '\d+'])]
    public function listByTask(int $taskId, Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $query = CommentQuery::fromRequest($request, $this->queryParams);
        $query->taskId = $taskId;
        $this->validator->validate($query);
        $comments = $this->commentService->getCommentsByTaskIdForUser($taskId, $user, $query);
        return new JsonResponse($this->serializer->serialize($comments, 'json', ['groups' => 'comment:read']), json: true);
    }

    #[Route('/tasks/{taskId}/comments', name: 'api_task_comments_create', methods: ['POST'], requirements: ['taskId' => '\d+'])]
    public function createForTask(int $taskId, Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $data = $this->payloadParser->parse($request);
        $data['taskId'] = $taskId;
        $dto = CreateCommentRequest::fromArray($data);
        $this->validator->validate($dto);
        $comment = $this->commentService->createComment($dto, $user);
        return new JsonResponse($this->serializer->serialize($comment, 'json', ['groups' => 'comment:read']), json: true);
    }
}
