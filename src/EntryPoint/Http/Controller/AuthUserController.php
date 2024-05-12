<?php
declare(strict_types=1);

namespace App\EntryPoint\Http\Controller;

use App\EntryPoint\Http\Contract\AbstractApiController;
use App\Modules\User\Application\ChangePassword\ChangePasswordRequest;
use App\Modules\User\Application\ChangePassword\ChangePasswordUseCase;
use App\Modules\User\Application\DeleteAuthUser\DeleteAuthUserRequest;
use App\Modules\User\Application\DeleteAuthUser\DeleteAuthUserUseCase;
use App\Modules\User\Application\LoginAuthUser\LoginAuthUserRequest;
use App\Modules\User\Application\LoginAuthUser\LoginAuthUserResponse;
use App\Modules\User\Application\LoginAuthUser\LoginAuthUserUseCase;
use App\Modules\User\Application\LogoutAuthUser\LogoutAuthUserRequest;
use App\Modules\User\Application\LogoutAuthUser\LogoutAuthUserUseCase;
use App\Modules\User\Application\RegisterAuthUser\RegisterAuthUserResponse;
use App\Modules\User\Application\RegisterAuthUser\RegisterAuthUserUseCase;
use App\Modules\User\Application\RegisterAuthUser\ResisterAuthUserRequest;
use App\Modules\User\Application\SignOutAuthUser\SignOutAuthUserRequest;
use App\Modules\User\Application\SignOutAuthUser\SignOutAuthUserUseCase;
use App\Modules\User\Application\UpdateUser\UpdateUserRequest;
use App\Modules\User\Application\UpdateUser\UpdateUserUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AuthUserController extends AbstractApiController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request, RegisterAuthUserUseCase $useCase
    ): \Symfony\Component\HttpFoundation\JsonResponse {
        $jsonData = $this->getRequestData($request, [
            'email',
            'password',
            'passwordConfirmation',
            'firstName',
            'lastName',
            'deviceName',
        ], [
            'email',
            'password',
            'passwordConfirmation',
        ]);

        $useCaseRequest = new ResisterAuthUserRequest($jsonData['email'], $jsonData['password'],
            $jsonData['passwordConfirmation'], $jsonData['firstName'], $jsonData['lastName'], $jsonData['deviceName']);

        /** @var RegisterAuthUserResponse $response */
        $response = $useCase->run($useCaseRequest);

        $data = $this->serializer->serialize([
            'user' => $response->user,
            'token' => $response->token,
        ], 'json', ['groups' => ['user']]);

        return $this->jsonResponse($data);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request, LoginAuthUserUseCase $useCase
    ): \Symfony\Component\HttpFoundation\JsonResponse {
        $jsonData = $this->getRequestData($request, [
            'email',
            'password',
            'deviceName',
        ], [
            'email',
            'password',
        ]);
        $useCaseRequest = new LoginAuthUserRequest($jsonData['email'], $jsonData['password'], $jsonData['deviceName']);

        /** @var LoginAuthUserResponse $response */
        $response = $useCase->run($useCaseRequest);

        $data = $this->serializer->serialize([
            'user' => $response->user,
            'token' => $response->token,
        ], 'json', ['groups' => ['user']]);

        return $this->jsonResponse($data);
    }

    #[Route('/account/logout/{tokenId}', name: 'logout', methods: ['DELETE'])]
    public function logout(string $tokenId, LogoutAuthUserUseCase $useCase): JsonResponse
    {
        $useCaseRequest = new LogoutAuthUserRequest($tokenId);

        /** @var \App\Modules\User\Application\LogoutAuthUser\LogoutAuthUserResponse $response */
        $response = $useCase->run($useCaseRequest);

        $data = $this->serializer->serialize([
            'user' => $response->user,
        ], 'json', ['groups' => ['user']]);

        return $this->jsonResponse($data);
    }

    #[Route('/account/me/sign-out', name: 'me.sign-out', methods: ['POST'])]
    public function signOut(SignOutAuthUserUseCase $useCase): JsonResponse
    {
        $userId = $this->ensureCurrentUserId();

        $useCaseRequest = new SignOutAuthUserRequest($userId);

        /** @var \App\Modules\User\Application\SignOutAuthUser\SignOutAuthUserResponse $response */
        $response = $useCase->run($useCaseRequest);

        $data = $this->serializer->serialize([
            'user' => $response->user,
        ], 'json', ['groups' => ['user']]);

        return $this->jsonResponse($data);
    }

    #[Route('/account/me/update', name: 'me.update', methods: ['PATCH'])]
    public function update(Request $request, UpdateUserUseCase $useCase): JsonResponse
    {
        $userId = $this->ensureCurrentUserId();

        $jsonData = $this->getRequestData($request, [
            'firstName',
            'lastName',
        ]);

        $useCaseRequest = new UpdateUserRequest($userId, $jsonData['firstName'], $jsonData['lastName']);

        /** @var \App\Modules\User\Application\UpdateUser\UpdateUserResponse $response */
        $response = $useCase->run($useCaseRequest);

        $data = $this->serializer->serialize([
            'user' => $response->user,
        ], 'json', ['groups' => ['user']]);

        return $this->jsonResponse($data);
    }

    #[Route('/account/me/change-password', name: 'me.change-password', methods: ['PATCH'])]
    public function changePassword(Request $request, ChangePasswordUseCase $useCase): JsonResponse
    {
        $userId = $this->ensureCurrentUserId();

        $jsonData = $this->getRequestData($request, [
            'currentPassword',
            'password',
            'passwordConfirmation',
        ], [
            'currentPassword',
            'password',
            'passwordConfirmation',
        ]);

        $useCaseRequest = new ChangePasswordRequest($userId, $jsonData['currentPassword'], $jsonData['password'],
            $jsonData['passwordConfirmation']);

        /** @var \App\Modules\User\Application\ChangePassword\ChangePasswordResponse $response */
        $response = $useCase->run($useCaseRequest);

        $data = $this->serializer->serialize([
            'user' => $response->user,
        ], 'json', ['groups' => ['user']]);

        return $this->jsonResponse($data);
    }

    #[Route('/account/me/delete-account', name: 'me.delete-account', methods: ['POST'])]
    public function deleteAccount(Request $request, DeleteAuthUserUseCase $useCase): JsonResponse
    {
        $userId = $this->ensureCurrentUserId();

        $jsonData = $this->getRequestData($request, [
            'password',
        ], [
            'password',
        ]);

        $useCaseRequest = new DeleteAuthUserRequest($userId, $jsonData['password']);

        /** @var \App\Modules\User\Application\DeleteAuthUser\DeleteAuthUserResponse $response */
        $response = $useCase->run($useCaseRequest);

        $data = $this->serializer->serialize($response, 'json');

        return $this->jsonResponse($data);
    }

}
