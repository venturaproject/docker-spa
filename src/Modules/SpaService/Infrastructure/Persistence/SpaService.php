<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Infrastructure\Persistence;

use App\Modules\Shared\Domain\Exception\ValidationException;
use App\Modules\SpaService\Domain\Contract\SpaServiceServiceInterface;
use App\Modules\SpaService\Domain\SpaService;
use App\Modules\SpaService\Infrastructure\Persistence\Doctrine\SpaServiceRepository;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException as SpaServiceNotFoundException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SpaServiceService implements SpaServiceServiceInterface
{
    private SpaServiceRepository $repository;
    private ValidatorInterface $validator;

    public function __construct(SpaServiceRepository $repository, ValidatorInterface $validator)
    {
        $this->repository = $repository;
        $this->validator = $validator;
    }

    public function create(string $name, float $price): SpaService
    {
        $spaService = SpaService::create($name, $price);

        $errors = $this->validator->validate($spaService);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $this->repository->save($spaService);
        return $spaService;
    }

    public function update(string $id, ?string $name, ?float $price): SpaService
    {
        $spaService = $this->repository->findSpaServiceById($id);
        if (!$spaService) {
            throw new SpaServiceNotFoundException("Spa Service not found.");
        }

        if ($name !== null) {
            $spaService->setName($name);
        }
        if ($price !== null) {
            $spaService->setPrice($price);
        }

        $this->repository->save($spaService);
        return $spaService;
    }

    public function delete(string $id): void
    {
        $spaService = $this->repository->findSpaServiceById($id);
        if (!$spaService) {
            throw new SpaServiceNotFoundException("Spa Service not found.");
        }

        $this->repository->delete($spaService);
    }

    public function findById(string $id): ?SpaService
    {
        return $this->repository->findSpaServiceById($id);
    }

    public function freshSpaServiceById(string $id): ?SpaService
    {
        $spaService = $this->repository->findSpaServiceById($id);
        if (!$spaService) {
            return null;
        }
        $this->repository->refresh($spaService);
        return $spaService;
    }
}
