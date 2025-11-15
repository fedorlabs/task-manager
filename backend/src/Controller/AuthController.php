<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\AuthService;
use App\Application\Service\EntitySerializer;
use App\Domain\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly EntitySerializer $serializer,
    ) {}

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['message' => 'The login and/or password are incorrect'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['token' => 'handled_by_lexik']);
    }

    #[Route('/signup', name: 'api_signup', methods: ['POST'])]
    public function signup(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $user = $this->authService->register(
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['isAdmin'] ?? false,
            $data['avatar'] ?? null,
        );

        return $this->json($this->serializer->serializeUser($user), Response::HTTP_OK);
    }

    #[Route('/whoAmI', name: 'api_whoami', methods: ['GET'])]
    public function whoAmI(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['message' => 'The user is not authorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($this->serializer->serializeUser($user));
    }

    #[Route('/logout', name: 'api_logout', methods: ['DELETE'])]
    public function logout(): JsonResponse
    {
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
