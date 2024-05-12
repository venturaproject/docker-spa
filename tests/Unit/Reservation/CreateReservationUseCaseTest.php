<?php


namespace App\Tests\Unit\Reservation;

use PHPUnit\Framework\TestCase;
use App\Modules\SpaService\Application\CreateReservation\CreateReservationUseCase;
use App\Modules\SpaService\Application\CreateReservation\CreateReservationRequest;
use App\Modules\SpaService\Domain\ServiceBooking;
use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\SpaServiceRepository;
use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\ServiceBookingRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreateReservationUseCaseTest extends TestCase
{
    private $spaServiceRepository;
    private $bookingRepository;
    private $entityManager;
    private $useCase;

    protected function setUp(): void
    {
        $this->spaServiceRepository = $this->createMock(SpaServiceRepository::class);
        $this->bookingRepository = $this->createMock(ServiceBookingRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->useCase = new CreateReservationUseCase($this->spaServiceRepository, $this->bookingRepository, $this->entityManager);
    }

    public function testExecuteSuccess()
    {
        $serviceId = '2b5d7cf2-6591-4631-b88e-33926dfeee30'; // UUID del servicio existente
        $request = new CreateReservationRequest($serviceId, 'John Doe', 'john.doe@example.com', new \DateTime('2024-01-01'), new \DateTime('10:00:00'));
        $response = $this->useCase->execute($request);
    
        $this->assertTrue($response->success);
        $this->assertEquals('Reservation created successfully.', $response->message);
        $this->assertInstanceOf(ServiceBooking::class, $response->booking);
    }
    
    public function testExecuteServiceNotFound()
    {
        $this->spaServiceRepository->method('find')->willReturn(null);

        $request = new CreateReservationRequest('999', 'John Doe', 'john.doe@example.com', new \DateTime('2024-01-01'), new \DateTime('10:00:00'));
        $response = $this->useCase->execute($request);

        $this->assertFalse($response->success);
        $this->assertEquals('Service not found.', $response->message);
    }

}