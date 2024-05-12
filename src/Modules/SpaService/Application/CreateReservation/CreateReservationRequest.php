<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Application\CreateReservation;

use App\Modules\Shared\Application\Contract\RequestInterface;

class CreateReservationRequest implements RequestInterface
{
    public string $serviceId;
    public string $clientName;
    public string $clientEmail;
    public \DateTime $serviceDay;
    public \DateTime $serviceTime;

    public function __construct(string $serviceId, string $clientName, string $clientEmail, \DateTime $serviceDay, \DateTime $serviceTime)
    {
        $this->serviceId = $serviceId;
        $this->clientName = $clientName;
        $this->clientEmail = $clientEmail;
        $this->serviceDay = $serviceDay;
        $this->serviceTime = $serviceTime;
    }
}