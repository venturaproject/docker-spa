<?php
declare (strict_types = 1);

namespace App\Modules\SpaService\Infrastructure\Persistence\Doctrine;

use App\Modules\SpaService\Domain\SpaService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SpaServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpaService::class);
    }

    public function save(SpaService $spa_service): void
    {
        $this->getEntityManager()->persist($spa_service);
        $this->getEntityManager()->flush();
    }

    public function delete(SpaService $spa_service): void
    {
        $this->getEntityManager()->remove($spa_service);
        $this->getEntityManager()->flush();
    }

    public function refresh(SpaService $spa_service): void
    {
        $this->getEntityManager()->refresh($spa_service);
    }

    public function findAllSpaServices(): array
    {
        return $this->findAll();
    }

    public function findSpaServiceById(string $id): ?SpaService
    {
        return $this->find($id);
    }

}
