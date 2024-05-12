<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Domain;

use Doctrine\ORM\Mapping as ORM;
use App\Modules\Shared\Domain\ValueObject\EntityId;

/**
 * @ORM\Entity
 * @ORM\Table(name="service_schedules")
 */
class ServiceSchedule
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=36)
     */
    private string $id;

    /**
     * @ORM\ManyToOne(targetEntity="SpaService", inversedBy="schedules")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private SpaService $spaService;

    /**
     * @ORM\Column(type="date")
     */
    private \DateTime $day;

    /**
     * @ORM\Column(type="time")
     */
    private \DateTime $startTime;

    /**
     * @ORM\Column(type="time")
     */
    private \DateTime $endTime;

    public function __construct(SpaService $spaService, \DateTime $day, \DateTime $startTime, \DateTime $endTime)
    {
        $this->id = EntityId::create()->getValue();
        $this->spaService = $spaService;
        $this->day = $day;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    public function getEndTime(): \DateTime
    {
        return $this->endTime;
    }
}

