<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Domain;

use Doctrine\ORM\Mapping as ORM;
use App\Modules\Shared\Domain\ValueObject\EntityId;

/**
 * @ORM\Entity
 * @ORM\Table(name="service_bookings")
 */
class ServiceBooking
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=36)
     */
    private string $id;

    /**
     * @ORM\ManyToOne(targetEntity="SpaService")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private SpaService $spaService;

    /**
     * @ORM\Column(type="string")
     */
    private string $clientName;

    /**
     * @ORM\Column(type="string")
     */
    private string $clientEmail;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTime $serviceDay;

    /**
     * @ORM\Column(type="time")
     */
    private \DateTime $serviceTime;

    public function __construct(SpaService $spaService, string $clientName, string $clientEmail, \DateTime $serviceDay, \DateTime $serviceTime)
    {
        $this->id = EntityId::create()->getValue();
        $this->spaService = $spaService;
        $this->clientName = $clientName;
        $this->clientEmail = $clientEmail;
        $this->serviceDay = $serviceDay;
        $this->serviceTime = $serviceTime;
    }

      // Añade getters y setters
      public function getId(): string
      {
          return $this->id;
      }
  
      public function getSpaService(): SpaService
      {
          return $this->spaService;
      }
  
      public function setSpaService(SpaService $spaService): void
      {
          $this->spaService = $spaService;
      }
  
      public function getClientName(): string
      {
          return $this->clientName;
      }
  
      public function setClientName(string $clientName): void
      {
          $this->clientName = $clientName;
      }
  
      public function getClientEmail(): string
      {
          return $this->clientEmail;
      }
  
      public function setClientEmail(string $clientEmail): void
      {
          $this->clientEmail = $clientEmail;
      }
  
      public function getServiceDay(): \DateTime
      {
          return $this->serviceDay;
      }
  
      public function setServiceDay(\DateTime $serviceDay): void
      {
          $this->serviceDay = $serviceDay;
      }
  
      public function getServiceTime(): \DateTime
      {
          return $this->serviceTime;
      }
  
      public function setServiceTime(\DateTime $serviceTime): void
      {
          $this->serviceTime = $serviceTime;
      }
  
}
