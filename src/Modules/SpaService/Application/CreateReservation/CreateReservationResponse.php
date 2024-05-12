<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Application\CreateReservation;

use App\Modules\Shared\Application\Contract\ResponseInterface;
use App\Modules\SpaService\Domain\ServiceBooking;

class CreateReservationResponse implements ResponseInterface
{
    public bool $success;
    public string $message;
    public ?ServiceBooking $booking;

    public function __construct(bool $success, string $message, ?ServiceBooking $booking = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->booking = $booking;
    }
}