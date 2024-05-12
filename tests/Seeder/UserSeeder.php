<?php

namespace App\Tests\Seeder;

use App\Modules\User\Domain\AuthToken;
use App\Modules\User\Domain\User;
use App\Modules\User\Infrastructure\Security\AuthUser;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserSeeder extends SeederBase
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(ObjectManager $manager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct($manager);

        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @param array $options
     * @param array $withTokens - if not empty, create a user ready to access private pages.
     * @return \App\Modules\User\Domain\User
     * @throws \Random\RandomException
     */
    public function seedUser(array $options = [], array $withTokens = []): User
    {
        $options = array_merge([
            'email' => 'test@example.com',
            'password' => 'password',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'roles' => ['ROLE_USER'],
        ], $options);

        $user = User::create($options['email'], $options['password'], $options['firstName'], $options['lastName'],
            $options['roles']);

        $authUser = new AuthUser($user);

        $hashedPassword = $this->passwordHasher->hashPassword($authUser, $options['password']);
        $user->setPassword($hashedPassword);

        $repository = $this->objectManager->getRepository(User::class);

        $repository->save($user);

        // We have a user at this point. No tokens were created yet, so user must log in
        // prior to access private pages.

        // Create device tokens if need be. We can pass multiple devices, so that multiple
        // tokens are created.
        // When in test method we can get user tokens: $user->getTokens()
        if ($withTokens) {
            $tokenRepository = $this->objectManager->getRepository(AuthToken::class);
            foreach ($withTokens as $withToken) {
                $token = bin2hex(random_bytes(32));

                $authToken = AuthToken::create($user, $token, $withToken['name'], $withToken['expiresAfter'] ?? null);
                $tokenRepository->save($authToken);

                $user->addAuthToken($authToken);
            }

            $repository->save($user);
        }

        return $user;
    }
}
