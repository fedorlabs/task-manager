<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\EntitySerializer;
use App\Application\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly EntitySerializer $serializer,
    ) {}

    #[Route('/users', name: 'api_users_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->serializer->serializeUsers($this->userService->getAllUsers()));
    }
}
