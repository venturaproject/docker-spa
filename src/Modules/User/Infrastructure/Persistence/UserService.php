<?php
declare(strict_types=1);

namespace App\Modules\User\Infrastructure\Persistence;

use App\Modules\Shared\Domain\Exception\ValidationException;
use App\Modules\User\Domain\Contract\UserServiceInterface;
use App\Modules\User\Domain\User;
use App\Modules\User\Infrastructure\Persistence\Doctrine\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService implements UserServiceInterface
{
    private UserRepository $repository;

    private ValidatorInterface $validator;

    public function __construct(UserRepository $repository, ValidatorInterface $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    /**
     * @throws \App\Modules\Shared\Domain\Exception\ValidationException
     */
    public function create(string $email, ?string $password, ?string $firstName, ?string $lastName, array $roles = []
    ): User {
        $user = User::create($email, $password, $firstName, $lastName, $roles);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $this->repository->save($user);

        return $user;
    }

    public function update(string $id, ?string $firstName, ?string $lastName): User
    {
        $user = $this->repository->find($id);
        $this->repository->refresh($user);

        if (is_null($user)) {
            throw new UserNotFoundException('User not found');
        }

        $user->setFirstName($firstName);
        $user->setLastName($lastName);

        $this->repository->save($user);

        return $user;
    }

    public function delete(string $id): void
    {
        $user = $this->repository->find($id);

        if (is_null($user)) {
            throw new UserNotFoundException('User not found');
        }

        // Delete from repository.
        $this->repository->delete($user);
    }

    public function save(User $user): User
    {
        $this->repository->save($user);

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function findById(string $id): ?User
    {
        return $this->repository->find($id);
    }

    public function freshUserById(string $id): ?User
    {
        $user = $this->repository->find($id);
        $this->repository->refresh($user);

        return $user;
    }

}
