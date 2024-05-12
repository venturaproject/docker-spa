<?php
declare (strict_types = 1);

namespace App\Modules\SpaService\Infrastructure\Persistence\Doctrine;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Modules\SpaService\Domain\ServiceSchedule;

class ServiceScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceSchedule::class);
    }

    public function findSchedulesByServiceAndDate(string $serviceId, string $date): array
    {
        $qb = $this->createQueryBuilder('s');
        $qb->where('s.spaService = :serviceId')
           ->andWhere('s.day = :date')
           ->setParameter('serviceId', $serviceId)
           ->setParameter('date', new \DateTime($date));
    
        return $qb->getQuery()->getResult();
    }
}