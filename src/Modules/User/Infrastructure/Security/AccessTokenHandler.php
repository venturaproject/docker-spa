<?php
declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Security;

use App\Modules\User\Infrastructure\Persistence\Doctrine\AuthTokenRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    private AuthTokenRepository $repository;

    public function __construct(AuthTokenRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        /** @var \App\Modules\User\Domain\AuthToken $accessToken */
        $accessToken = $this->repository->findOneBy(['token' => $accessToken]);
        if (null === $accessToken || ! $accessToken->isValid()) {
            throw new BadCredentialsException('Invalid credentials.', Response::HTTP_UNAUTHORIZED);
        }

        // Touch last-used timestamp.
        $accessToken->setLastUsedAt(new \DateTime());
        $this->repository->save($accessToken);

        // We decouple domain user and auth-user, so have to provide user loader closure.
        return new UserBadge($accessToken->getUser()->getUserIdentifier(), function () use ($accessToken) {
            return new AuthUser($accessToken->getUser());
        });
    }

}
