<?php
declare(strict_types=1);

namespace App\Modules\SpaService\Domain;

use App\Modules\Shared\Domain\ValueObject\EntityId;

class SpaService
{
    private string $id;
    private string $name;
    private float $price;
    private \DateTime $createdAt;

    public function __construct(
        EntityId $id,
        string $name,
        float $price,
        \DateTime $createdAt
    ) {
        $this->id = $id->getValue();
        $this->name = $name;
        $this->price = $price;
        $this->createdAt = $createdAt;
    }

    public static function create(
        string $name,
        float $price
    ): SpaService {
        return new self(EntityId::create(), $name, $price, new \DateTime());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
