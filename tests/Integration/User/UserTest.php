<?php

namespace App\Tests\Integration\User;

use App\Modules\Shared\Domain\Exception\ValidationException;
use App\Modules\User\Application\CreateUser\CreateUserRequest;
use App\Modules\User\Application\CreateUser\CreateUserUseCase;
use App\Modules\User\Application\UpdateUser\UpdateUserRequest;
use App\Modules\User\Application\UpdateUser\UpdateUserUseCase;
use App\Tests\DatabaseTestCase;
use App\Tests\Seeder\UserSeeder;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class UserTest extends DatabaseTestCase
{
    public function test_create_user_use_case(): void
    {
        $container = static::getContainer();

        $request = new CreateUserRequest('test@test.com', 'password', 'First', 'Last');

        /** @var CreateUserUseCase $useCase */
        $useCase = $container->get(CreateUserUseCase::class);
        $response = $useCase->run($request);

        $this->assertEquals('test@test.com', $response->user->getEmail());
    }

    public function test_update_user_use_case(): void
    {
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser();

        $request = new UpdateUserRequest($user->getId(), 'First', 'Last');

        /** @var UpdateUserUseCase $useCase */
        $useCase = $container->get(UpdateUserUseCase::class);
        $response = $useCase->run($request);

        $this->assertEquals('test@example.com', $response->user->getEmail());
        $this->assertEquals('First', $response->user->getFirstName());
        $this->assertEquals('Last', $response->user->getLastName());
    }

    public function test_create_user_use_case_error_email_already_taken(): void
    {
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $seeder->seedUser([
            'email' => 'test@test.com',
        ]);

        $request = new CreateUserRequest('test@test.com', 'password', 'First', 'Last');

        /** @var CreateUserUseCase $useCase */
        $useCase = $container->get(CreateUserUseCase::class);

        $this->expectException(ValidationException::class);
        $useCase->run($request);
    }
}
