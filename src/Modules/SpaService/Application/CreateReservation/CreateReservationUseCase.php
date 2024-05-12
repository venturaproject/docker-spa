<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Application\CreateReservation;

use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\ServiceBookingRepository;
use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\SpaServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Modules\SpaService\Domain\ServiceBooking;

class CreateReservationUseCase
{
    private SpaServiceRepository $spaServiceRepository;
    private ServiceBookingRepository $bookingRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(SpaServiceRepository $spaServiceRepository, ServiceBookingRepository $bookingRepository, EntityManagerInterface $entityManager)
    {
        $this->spaServiceRepository = $spaServiceRepository;
        $this->bookingRepository = $bookingRepository;
        $this->entityManager = $entityManager;
    }

    public function execute(CreateReservationRequest $request): CreateReservationResponse
    {
        $spaService = $this->spaServiceRepository->find($request->serviceId);
        if (!$spaService) {
            return new CreateReservationResponse(false, "Service not found.");
        }

        if (!$this->bookingRepository->isServiceAvailable($request->serviceId, $request->serviceDay, $request->serviceTime)) {
            return new CreateReservationResponse(false, "Service not available at the requested time.");
        }
    
        $booking = new ServiceBooking($spaService, $request->clientName, $request->clientEmail, $request->serviceDay, $request->serviceTime);
        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return new CreateReservationResponse(true, "Reservation created successfully.", $booking);
    }
}
