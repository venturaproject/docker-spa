<?php
declare (strict_types = 1);

// src/Modules/SpaService/Infrastructure/Persistence/Doctrine/ServiceBookingRepository.php

namespace App\Modules\SpaService\Infrastructure\Persistence\Doctrine;

use App\Modules\SpaService\Domain\ServiceBooking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceBookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceBooking::class);
    }

    public function findBookingsByServiceAndDate(string $serviceId, string $date): array
    {
        $qb = $this->createQueryBuilder('b');
        $qb->where('b.spaService = :serviceId')
            ->andWhere('b.serviceDay = :date')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('date', new \DateTime($date));

        return $qb->getQuery()->getResult();
    }

    public function isServiceAvailable(string $serviceId, \DateTime $serviceDay, \DateTime $serviceTime): bool
    {
        $qb = $this->createQueryBuilder('b');
        $qb->where('b.spaService = :serviceId')
            ->andWhere('b.serviceDay = :day')
            ->andWhere('b.serviceTime = :time')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('day', $serviceDay->format('Y-m-d'))
            ->setParameter('time', $serviceTime->format('H:i:s'));

        $result = $qb->getQuery()->getResult();
        return empty($result); // Devuelve true si no hay reservas, false si hay alguna
    }
}
