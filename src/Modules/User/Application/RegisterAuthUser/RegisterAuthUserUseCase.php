<?php
declare(strict_types=1);

namespace App\Modules\User\Application\RegisterAuthUser;

use App\Modules\Shared\Application\Contract\RequestInterface;
use App\Modules\Shared\Application\Contract\ResponseInterface;
use App\Modules\Shared\Application\Contract\UseCaseInterface;
use App\Modules\User\Domain\Contract\AuthUserServiceInterface;

class RegisterAuthUserUseCase implements UseCaseInterface
{
    private AuthUserServiceInterface $service;

    public function __construct(AuthUserServiceInterface $service)
    {
        $this->service = $service;
    }

    public function run(RequestInterface $request): ResponseInterface
    {
        /** @var \App\Modules\User\Application\RegisterAuthUser\ResisterAuthUserRequest $request */
        [$user, $token] = $this->service->register($request->email, $request->password,
            $request->passwordConfirmation, $request->firstName, $request->lastName, $request->deviceName);

        $response = new RegisterAuthUserResponse();

        $response->token = $token;
        $response->user = $user;

        return $response;
    }
}
