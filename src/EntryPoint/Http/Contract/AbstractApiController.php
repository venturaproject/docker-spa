<?php
declare(strict_types=1);

namespace App\EntryPoint\Http\Contract;

use App\Modules\User\Domain\Contract\UserServiceInterface;
use App\Modules\User\Domain\User;
use App\Modules\User\Infrastructure\Security\AuthUser;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Serializer\SerializerInterface;

class AbstractApiController extends AbstractController
{
    protected LoggerInterface $logger;

    protected SerializerInterface $serializer;

    public function __construct(LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * @param string $data
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function jsonResponse(string $data = ''): JsonResponse
    {
        return JsonResponse::fromJsonString($data);
    }

    /**
     * Include current user in response.
     *
     * @param string $data
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function jsonResponseWithUser(string $data = ''): JsonResponse
    {
        $data = json_decode($data);
        $user = $this->getUser();
        if ($user instanceof AuthUser) {
            /** @var AuthUser $user */
            $user = $user->getUser();
        }

        if ($user) {
            $user = $this->serializer->normalize($user, 'json', ['groups' => ['user']]);
        }

        return $this->json(['data' => $data, 'user' => $user]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $expectedKeys
     * @param array $mandatory
     * @return array
     */
    public function getRequestData(Request $request, array $expectedKeys, array $mandatory = []): array
    {
        $content = json_decode($request->getContent(), true);

        if (! $content) {
            throw new BadRequestException('Request content is empty or not valid');
        }

        $data = [];
        foreach ($expectedKeys as $key) {
            $data[$key] = $content[$key] ?? null;
        }

        if ($mandatory) {
            foreach ($mandatory as $key) {
                if (! isset($data[$key])) {
                    throw new BadRequestException('Mandatory key '.$key.' is missing');
                }
            }
        }

        return $data;
    }

    /**
     * Get currently logged-in user.
     * (RESERVED)
     *
     * @param \App\Modules\User\Domain\Contract\UserServiceInterface $service
     * @return \App\Modules\User\Domain\User|null
     */
    public function getCurrentUser(UserServiceInterface $service): ?User
    {
        $authUser = $this->getUser();
        if ($authUser instanceof AuthUser) {
            $userId = $authUser->getUser()->getId();

            return $service->freshUserById($userId);
        }

        return null;
    }

    /**
     * @return string
     */
    protected function ensureCurrentUserId(): string
    {
        $id = $this->getUser()?->getId();

        if (is_null($id)) {
            throw new UnauthorizedHttpException('You must be logged in.');
        }

        return $id;
    }
}
