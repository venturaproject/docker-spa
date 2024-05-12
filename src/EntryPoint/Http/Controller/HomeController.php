<?php

declare (strict_types = 1);

namespace App\EntryPoint\Http\Controller;

use App\EntryPoint\Http\Contract\AbstractApiController;
use App\Modules\User\Application\RegisterAuthUser\RegisterAuthUserUseCase;
use App\Modules\User\Infrastructure\Persistence\Doctrine\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class HomeController extends AbstractApiController
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Route('/', name: 'welcome', methods: ['GET'])]
    public function welcome(RegisterAuthUserUseCase $useCase): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to the home page.',
        ]);
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to dashboard. You are logged in.',
        ]);
    }

    #[Route('/users', name: 'users', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $users = $this->userRepository->findAllUsers();

        // Transformar los objetos User a un array que pueda ser convertido a JSON
        $userData = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                // Añade aquí más campos según necesites
            ];
        }, $users);

        return $this->json([
            'users' => $userData,
        ]);
    }

    #[Route('/users/{id}', name: 'get_user_by_id', methods: ['GET'])]
    public function getUserById(string $id): JsonResponse
    {
        $user = $this->userRepository->findUserById($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            // Añade más atributos según sea necesario
        ]);
    }

}
