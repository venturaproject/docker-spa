<?php

namespace App\Tests\Functional;

use App\Modules\User\Domain\AuthToken;
use App\Modules\User\Domain\User;
use App\Tests\DatabaseTestCase;
use App\Tests\Seeder\UserSeeder;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class SampleTest extends DatabaseTestCase
{
    public function test_example(): void
    {
        $client = self::getReusableClient();
        $client->jsonRequest('GET', '/api/');

        // $response = json_decode($client->getResponse()->getContent());

        $this->assertResponseIsSuccessful();
    }

    public function test_we_can_access_private_page(): void
    {
        $container = static::getContainer();
        $passwordHasher = $container->get(UserPasswordHasher::class);

        $seeder = new UserSeeder($this->getEntityManager(), $passwordHasher);
        $user = $seeder->seedUser([], [
            ['name' => 'web', 'expiresAfter' => 24 * 60],
        ]);

        $token = $user->getAuthTokens()[0];

        $client = self::getReusableClient();

        $client->jsonRequest('GET', '/api/dashboard', [], [
            'HTTP_Authorization' => 'Bearer ' . $token->getToken(),
        ]);

        $response = json_decode($client->getResponse()->getContent());

        $this->assertResponseIsSuccessful();
        // $this->assertEquals('Welcome to dashboard. You are logged in.', $response->data->message);
        // $this->assertEquals('test@example.com', $response->user->email);
    }

    public function test_we_cannot_access_private_page_when_unauthorized(): void
    {
        $user = User::create('test@example.com', 'hashed_password', 'First', 'Last', ['ROLE_USER']);
        $accessToken = AuthToken::create($user, 'secret_token', 'web');

        $tokenRepository = $this->getRepository(AuthToken::class);
        $tokenRepository->save($accessToken);

        $client = self::getReusableClient();

        $client->jsonRequest('GET', '/api/dashboard', [], [
            'HTTP_Authorization' => 'Bearer ' . 'wrong_token',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllUsers(): void
    {
        $client = self::getReusableClient();
        $client->jsonRequest('GET', '/api/users');

        $this->assertResponseIsSuccessful();
        $response = json_decode($client->getResponse()->getContent(), true);

        // Asegúrate de que la respuesta es un array y contiene los elementos esperados
        $this->assertIsArray($response);
        // Aquí puedes añadir más aserciones específicas sobre los datos devueltos
    }
}
