<?php
declare (strict_types = 1);

namespace App\Modules\User\Infrastructure\Persistence\Doctrine;

use App\Modules\User\Domain\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function delete(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    public function refresh(User $user): void
    {
        $this->getEntityManager()->refresh($user);
    }

    public function findAllUsers(): array
    {
        return $this->findAll();
    }

    public function findUserById(string $id): ?User
    {
        return $this->find($id);
    }

}
