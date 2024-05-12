<?php

declare(strict_types=1);

namespace App\Modules\SpaService\Domain\Contract;

use App\Modules\SpaService\Domain\SpaService;

interface SpaServiceInterface
{
    public function create(string $name, float $price): SpaService;

    public function update(string $id, string $name): ?SpaService;

    public function delete(string $id): void;

    public function save(SpaService $spaService): SpaService;

    public function findByName(string $name): ?SpaService;

    public function findById(string $id): ?SpaService;

    /**
     * Retrieves a fresh instance of the SpaService by ID, bypassing any cache.
     */
    public function freshSpaServiceById(string $id): ?SpaService;
}
