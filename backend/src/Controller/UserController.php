<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\Query\UserQuery;
use App\Application\Service\QueryParamResolver;
use App\Application\Service\RequestValidator;
use App\Application\Service\UserService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Users', description: 'Управление пользователями')]
class UserController extends ApiController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly QueryParamResolver $queryParams,
        private readonly RequestValidator $validator,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/users', name: 'api_users_list', methods: ['GET'])]
    #[OA\Get(
        path: '/users',
        summary: 'Список пользователей',
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'order', in: 'query', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
            new OA\Parameter(name: 'offset', in: 'query', schema: new OA\Schema(type: 'integer', default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Список пользователей'),
            new OA\Response(response: 401, description: 'Не авторизован'),
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        $query = UserQuery::fromRequest($request, $this->queryParams);
        $this->validator->validate($query);

        return new JsonResponse($this->serializer->serialize($this->userService->getUsers($query), 'json', ['groups' => 'user:read']), json: true);
    }
}
