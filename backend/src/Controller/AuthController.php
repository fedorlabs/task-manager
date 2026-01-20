<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Dto\Auth\SignupRequest;
use App\Application\Service\AuthService;
use App\Application\Service\RequestPayloadParser;
use App\Application\Service\RequestValidator;
use App\Domain\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[OA\Tag(name: 'Auth', description: 'Аутентификация и авторизация')]
class AuthController extends ApiController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly RequestValidator $validator,
        private readonly RequestPayloadParser $payloadParser,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    #[OA\Post(
        path: '/login',
        summary: 'Вход в систему',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Успешный вход', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'isAdmin', type: 'boolean'),
                new OA\Property(property: 'avatar', type: 'string', nullable: true),
            ])),
            new OA\Response(response: 400, description: 'Неверные учетные данные'),
        ]
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['message' => 'The login and/or password are incorrect'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serializer->serialize($user, 'json', ['groups' => 'user:read']), json: true);
    }

    #[Route('/signup', name: 'api_signup', methods: ['POST'])]
    #[OA\Post(
        path: '/signup',
        summary: 'Регистрация пользователя',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Пользователь создан', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'isAdmin', type: 'boolean'),
                new OA\Property(property: 'avatar', type: 'string', nullable: true),
            ])),
            new OA\Response(response: 400, description: 'Ошибка валидации'),
        ]
    )]
    public function signup(Request $request): JsonResponse
    {
        $data = $this->payloadParser->parse($request);
        $dto = SignupRequest::fromArray($data);
        $this->validator->validate($dto);
        $user = $this->authService->register($dto);

        return new JsonResponse($this->serializer->serialize($user, 'json', ['groups' => 'user:read']), json: true);
    }

    #[Route('/whoAmI', name: 'api_whoami', methods: ['GET'])]
    #[OA\Get(
        path: '/whoAmI',
        summary: 'Текущий пользователь',
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Данные пользователя', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'isAdmin', type: 'boolean'),
                new OA\Property(property: 'avatar', type: 'string', nullable: true),
            ])),
            new OA\Response(response: 401, description: 'Не авторизован'),
        ]
    )]
    public function whoAmI(): JsonResponse
    {
        return new JsonResponse($this->serializer->serialize($this->getCurrentUser(), 'json', ['groups' => 'user:read']), json: true);
    }

    #[Route('/logout', name: 'api_logout', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/logout',
        summary: 'Выход из системы',
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(response: 204, description: 'Успешный выход'),
        ]
    )]
    public function logout(): JsonResponse
    {
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
