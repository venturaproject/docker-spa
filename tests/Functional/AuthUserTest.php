<?php

namespace App\Tests\Functional;

use App\Modules\User\Domain\AuthToken;
use App\Modules\User\Domain\User;
use App\Tests\DatabaseTestCase;
use App\Tests\Seeder\UserSeeder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class AuthUserTest extends DatabaseTestCase
{
    public function test_we_can_register_a_user(): void
    {
        $client = self::getReusableClient();

        $client->jsonRequest('POST', '/api/register', [
            'email' => 'test@example.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
            'firstName' => 'First',
            'lastName' => 'Last',
            'deviceName' => 'iPhone 15',
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertResponseIsSuccessful();

        $this->assertEquals('test@example.com', $response->user->email);

        $userRepository = $this->getRepository(User::class);
        $users = $userRepository->findAll();

        $this->assertCount(1, $users);
        $this->assertEquals('test@example.com', $users[0]->getEmail());

        $roles = $users[0]->getRoles();
        $this->assertCount(1, $roles);

        $tokenRepository = $this->getRepository(AuthToken::class);
        $tokens = $tokenRepository->findAll();

        $this->assertCount(1, $tokens);

        $this->assertEquals('iPhone 15', $tokens[0]->getName());
        $this->assertEquals('test@example.com', $tokens[0]->getUser()->getEmail());
    }

    public function test_register_a_user_error_duplicate_email(): void
    {
        $client = self::getReusableClient();
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $seeder->seedUser([
            'email' => 'test@example.com',
            'password' => 'password',
            'firstName' => 'First',
            'lastName' => 'Last',
            'roles' => ['ROLE_USER'],
        ]);

        $client->jsonRequest('POST', '/api/register', [
            'email' => 'test@example.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
            'firstName' => 'First',
            'lastName' => 'Last',
            'deviceName' => 'iPhone 15',
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals('Validation failed.', $response->message);
        $this->assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertCount(1, $response->errors);
        $this->assertEquals('email', $response->errors[0]->property);
    }

    public function test_register_a_user_error_missing_password(): void
    {
        $client = self::getReusableClient();

        $client->jsonRequest('POST', '/api/register', [
            'email' => 'test@example.com',
            'password' => null,
            'passwordConfirmation' => 'password',
            'firstName' => 'First',
            'lastName' => 'Last',
            'deviceName' => 'iPhone 15',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_we_can_login_a_user(): void
    {
        $client = self::getReusableClient();
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $seeder->seedUser([
            'email' => 'test@example.com',
            'password' => 'password',
            'firstName' => 'First',
            'lastName' => 'Last',
            'roles' => ['ROLE_USER'],
        ]);

        $client->jsonRequest('POST', '/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'deviceName' => 'iPhone 15',
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals('test@example.com', $response->user->email);
        $this->assertNotEmpty($response->token);

        $tokenRepository = $this->getRepository(AuthToken::class);
        $tokens = $tokenRepository->findAll();

        $this->assertCount(1, $tokens);

        $this->assertEquals('iPhone 15', $tokens[0]->getName());
        $this->assertEquals('test@example.com', $tokens[0]->getUser()->getEmail());
    }

    public function test_login_error_invalid_credentials(): void
    {
        $client = self::getReusableClient();
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $seeder->seedUser([
            'email' => 'test@example.com',
            'password' => 'password',
            'firstName' => 'First',
            'lastName' => 'Last',
            'roles' => ['ROLE_USER'],
        ]);

        $client->jsonRequest('POST', '/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            'deviceName' => 'iPhone 15',
        ]);

        $this->assertResponseStatusCodeSame(401);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals('Invalid credentials', $response->message);
        $this->assertFalse(isset($response->token));
    }

    public function test_we_can_logout_from_device(): void
    {
        $container = static::getContainer();
        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([], [
            ['name' => 'iPhone 15', 'expiresAfter' => null],
            ['name' => 'iPad', 'expiresAfter' => null],
        ]);

        $tokens = $user->getAuthTokens();

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('DELETE', '/api/account/logout/'.$tokens[1]->getId(), [], [
            'HTTP_Authorization' => 'Bearer '.$token->getToken(),
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent());
        $this->assertCount(1, $response->user->authTokens);
        $this->assertEquals('iPhone 15', $response->user->authTokens[0]->name);

        $tokenRepository = $this->getRepository(AuthToken::class);
        $tokens = $tokenRepository->findAll();
        $this->assertCount(1, $tokens);
        $this->assertEquals('iPhone 15', $tokens[0]->getName());

        $userRepository = $this->getRepository(User::class);
        $users = $userRepository->findAll();
        $this->assertNotEmpty($users[0]->getPassword());
    }

    public function test_we_can_sign_out_a_user(): void
    {
        $container = static::getContainer();
        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([], [
            ['name' => 'iPhone 15', 'expiresAfter' => null],
            ['name' => 'iPad', 'expiresAfter' => null],
        ]);

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('POST', '/api/account/me/sign-out', [], [
            'HTTP_Authorization' => 'Bearer '.$token->getToken(),
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent());
        $this->assertCount(0, $response->user->authTokens);

        $tokenRepository = $this->getRepository(AuthToken::class);
        $tokens = $tokenRepository->findAll();
        $this->assertCount(0, $tokens);

        $userRepository = $this->getRepository(User::class);
        $users = $userRepository->findAll();
        $this->assertNotEmpty($users[0]->getPassword());
    }

    public function test_we_can_update_a_user(): void
    {
        $container = static::getContainer();
        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([
            'firstName' => 'First',
            'lastName' => 'Last',
        ], [
            ['name' => 'iPhone 15', 'expiresAfter' => null],
            ['name' => 'iPad', 'expiresAfter' => null],
        ]);

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('PATCH', '/api/account/me/update', [
            'firstName' => 'First Modified',
            'lastName' => 'Last Modified',
        ], [
            'HTTP_Authorization' => 'Bearer '.$token->getToken(),
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals('First Modified', $response->user->firstName);
        $this->assertEquals('Last Modified', $response->user->lastName);

        $userRepository = $this->getRepository(User::class);
        $users = $userRepository->findAll();
        $this->assertNotEmpty($users[0]->getPassword());
    }

    public function test_a_user_can_change_password()
    {
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([
            'email' => 'test@test.com',
        ], [
            ['name' => 'iPhone 15', 'expiresAfter' => null],
        ]);

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('PATCH', '/api/account/me/change-password', [
            'currentPassword' => 'password',
            'password' => 'new-password',
            'passwordConfirmation' => 'new-password',
        ], [
            'HTTP_Authorization' => 'Bearer '.$token->getToken(),
        ]);

        $this->assertResponseIsSuccessful();

        $response = json_decode($client->getResponse()->getContent());

        // Change password action doesn't affect auth tokens.
        $this->assertCount(1, $response->user->authTokens);
    }

    public function test_change_password_fails_if_invalid_current_password(): void
    {
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([
            'email' => 'test@test.com',
        ], [
            ['name' => 'iPhone 15', 'expiresAfter' => null],
        ]);

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('PATCH', '/api/account/me/change-password', [
            'currentPassword' => 'wrong-password',
            'password' => 'new-password',
            'passwordConfirmation' => 'new-password',
        ], [
            'HTTP_Authorization' => 'Bearer '.$token->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function test_change_password_fails_if_wrong_password_confirmation(): void
    {
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([
            'email' => 'test@test.com',
        ], [
            ['name' => 'iPhone 15', 'expiresAfter' => null],
        ]);

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('PATCH', '/api/account/me/change-password', [
            'currentPassword' => 'password',
            'password' => 'new-password',
            'passwordConfirmation' => 'wrong-new-password',
        ], [
            'HTTP_Authorization' => 'Bearer '.$token->getToken(),
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function test_a_user_can_delete_account()
    {
        $container = static::getContainer();

        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([
            'email' => 'test@test.com',
        ], [
            ['name' => 'iPhone 15', 'expiresAfter' => null],
        ]);

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('POST', '/api/account/me/delete-account', [
            'password' => 'password',
        ], [
            'HTTP_Authorization' => 'Bearer '.$token->getToken(),
        ]);

        $this->assertResponseIsSuccessful();
    }
}
