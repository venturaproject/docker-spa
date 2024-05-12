<?php

declare (strict_types = 1);

namespace App\EntryPoint\Http\Controller;

use App\Modules\SpaService\Application\CreateReservation\CreateReservationRequest;
use App\Modules\SpaService\Application\CreateReservation\CreateReservationUseCase;
use App\Modules\SpaService\Application\ServiceAvailabilityQuery;
use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\SpaServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/spa_services')]
class SpaServiceController extends AbstractController
{
    private ServiceAvailabilityQuery $availabilityQuery;
    private SpaServiceRepository $spaServiceRepository;
    private CreateReservationUseCase $createReservationUseCase;

    public function __construct(
        ServiceAvailabilityQuery $availabilityQuery,
        SpaServiceRepository $spaServiceRepository,
        CreateReservationUseCase $createReservationUseCase
    ) {
        $this->availabilityQuery = $availabilityQuery;
        $this->spaServiceRepository = $spaServiceRepository;
        $this->createReservationUseCase = $createReservationUseCase;
    }

    #[Route('/', name: 'spa_services_list', methods: ['GET'])]
    public function listSpaServices(): JsonResponse
    {
        $spaServices = $this->spaServiceRepository->findAll();
        $spaServiceData = array_map(function ($spaService) {
            return [
                'id' => $spaService->getId(),
                'name' => $spaService->getName(),
                'price' => $spaService->getPrice(),
                'created_at' => $spaService->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $spaServices);

        return $this->json(['spa_services' => $spaServiceData]);
    }

    #[Route('/{id}/availability/{date}', name: 'service_availability', methods: ['GET'])]
    public function getAvailableTimes(Request $request, string $id, string $date): JsonResponse
    {
        // Convertir la fecha de string a objeto DateTime
        try {
            $dateObject = new \DateTime($date);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $availability = $this->availabilityQuery->getAvailableTimes($id, $dateObject->format('Y-m-d'));
        return $this->json(['data' => $availability]);
    }

    #[Route('/reservations', name: 'create_reservation', methods: ['POST'])]
    public function createReservation(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['serviceId'], $data['clientName'], $data['clientEmail'], $data['serviceDay'], $data['serviceTime'])) {
            return $this->json(['error' => 'Missing required parameters'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $createRequest = new CreateReservationRequest(
            $data['serviceId'],
            $data['clientName'],
            $data['clientEmail'],
            new \DateTime($data['serviceDay']),
            new \DateTime($data['serviceTime'])
        );

        $response = $this->createReservationUseCase->execute($createRequest);

        if ($response->success) {
            return $this->json([
                'message' => $response->message,
                'booking' => [
                    'id' => $response->booking->getId(),
                    'serviceDay' => $response->booking->getServiceDay()->format('Y-m-d'),
                    'serviceTime' => $response->booking->getServiceTime()->format('H:i:s'),
                ],
            ], JsonResponse::HTTP_CREATED);
        } else {
            return $this->json(['message' => $response->message], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
