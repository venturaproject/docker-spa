<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Application;

use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\ServiceScheduleRepository;
use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\ServiceBookingRepository;

class ServiceAvailabilityQuery
{
    private ServiceScheduleRepository $scheduleRepository;
    private ServiceBookingRepository $bookingRepository;

    public function __construct(ServiceScheduleRepository $scheduleRepository, ServiceBookingRepository $bookingRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
        $this->bookingRepository = $bookingRepository;
    }

    public function getAvailableTimes(string $serviceId, string $date): array
    {
        $schedules = $this->scheduleRepository->findSchedulesByServiceAndDate($serviceId, $date);
        $bookings = $this->bookingRepository->findBookingsByServiceAndDate($serviceId, $date);

        $bookedTimes = array_map(function ($booking) {
            return $booking->getServiceTime()->format('H:i');
        }, $bookings);

        $availableTimes = array_filter($schedules, function ($schedule) use ($bookedTimes) {
            return !in_array($schedule->getStartTime()->format('H:i'), $bookedTimes);
        });

        return array_map(function ($schedule) {
            return [
                'start_time' => $schedule->getStartTime()->format('H:i'),
                'end_time' => $schedule->getEndTime()->format('H:i')
            ];
        }, $availableTimes);
    }
}
