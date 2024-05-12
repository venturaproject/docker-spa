<?php
declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Security;

use App\Modules\Shared\Domain\Exception\ValidationException;
use App\Modules\User\Domain\Contract\AuthTokenServiceInterface;
use App\Modules\User\Domain\Contract\AuthUserServiceInterface;
use App\Modules\User\Domain\Contract\UserServiceInterface;
use App\Modules\User\Domain\User;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthUserService implements AuthUserServiceInterface
{
    private UserServiceInterface $userService;

    private UserPasswordHasherInterface $passwordHasher;

    private AuthTokenServiceInterface $tokenService;

    private ValidatorInterface $validator;

    public function __construct(
        UserServiceInterface $userService, UserPasswordHasherInterface $passwordHasher,
        AuthTokenServiceInterface $tokenService, ValidatorInterface $validator
    ) {
        $this->userService = $userService;
        $this->passwordHasher = $passwordHasher;
        $this->tokenService = $tokenService;
        $this->validator = $validator;
    }

    /**
     * @param string $email
     * @param string $password
     * @param string $passwordConfirmation
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $deviceName
     * @return array
     * @throws \App\Modules\Shared\Domain\Exception\ValidationException
     */
    public function register(
        string $email, string $password, string $passwordConfirmation, ?string $firstName, ?string $lastName,
        ?string $deviceName = null
    ): array {
        // TODO: Leverage Symfony validator for current request - there we can validate both password length and match with confirmation.
        if ($password !== $passwordConfirmation) {
            throw new BadRequestException('Passwords do not match');
        }

        $user = User::create($email, $password, $firstName, $lastName, ['ROLE_USER']);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $authUser = new AuthUser($user);

        $hashedPassword = $this->passwordHasher->hashPassword($authUser, $password);
        $user->setPassword($hashedPassword);

        $deviceName = $deviceName ?? 'web';
        $authToken = $this->tokenService->generateAndSaveToken($user, $deviceName);

        $user->addAuthToken($authToken);

        $this->userService->save($user);

        return [$user, $authToken->getToken()];
    }

    public function login(string $email, string $password, ?string $deviceName = null): array
    {
        $user = $this->userService->findByEmail($email);

        if (is_null($user)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $authUser = new AuthUser($user);

        if (! $this->passwordHasher->isPasswordValid($authUser, $password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $deviceName = $deviceName ?? 'web';

        $existing = $this->tokenService->existing($user, $deviceName);

        if (! is_null($existing)) {
            $this->tokenService->delete($existing);
        }

        $authToken = $this->tokenService->generateAndSaveToken($user, $deviceName);

        $user->addAuthToken($authToken);
        $this->userService->save($user);

        return [$user, $authToken->getToken()];
    }

    public function logout(string $tokenId): User
    {
        /** @var ?\App\Modules\User\Domain\AuthToken $token */
        $token = $this->tokenService->find($tokenId);
        if (is_null($token)) {
            throw new ResourceNotFoundException('Token not found');
        }

        $userId = $token->getUser()->getId();
        $user = $this->userService->freshUserById($userId);

        $user->removeAuthToken($token);
        $this->userService->save($user);

        $this->tokenService->delete($token);

        return $user;
    }

    /**
     * @param string $userId
     * @return \App\Modules\User\Domain\User
     */
    public function signOut(string $userId): User
    {
        $user = $this->userService->freshUserById($userId);

        if (is_null($user)) {
            throw new ResourceNotFoundException('User not found');
        }

        $user->removeAllAuthTokens();
        $this->userService->save($user);

        return $user;
    }

    public function changePassword(
        string $userId, string $currentPassword, string $password, string $passwordConfirmation
    ): User {
        // When we change password we check that the current password provided in request is valid.
        // We must get fresh user here because user password was already erased in
        // authentication manager.
        $user = $this->userService->freshUserById($userId);

        if (is_null($user)) {
            throw new UserNotFoundException('User not found');
        }

        $authUser = new AuthUser($user);

        if (! $this->passwordHasher->isPasswordValid($authUser, $currentPassword)) {
            throw new AccessDeniedHttpException('Invalid credentials');
        }

        if ($password !== $passwordConfirmation) {
            throw new BadRequestException('Passwords do not match');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($authUser, $password);
        $user->setPassword($hashedPassword);

        $this->userService->save($user);

        return $user;
    }

    /**
     * @param string $id
     * @param string $password
     * @return void
     */
    public function deleteAccount(string $id, string $password): void
    {
        // When we delete account we check that the password provided in request is valid.
        // We must get fresh user here because user password was already erased in
        // authentication manager.
        $user = $this->userService->freshUserById($id);

        if (is_null($user)) {
            throw new UserNotFoundException('User not found');
        }

        $authUser = new AuthUser($user);

        if (! $this->passwordHasher->isPasswordValid($authUser, $password)) {
            throw new AccessDeniedHttpException('Invalid password');
        }

        $this->userService->delete($id);
    }
}
