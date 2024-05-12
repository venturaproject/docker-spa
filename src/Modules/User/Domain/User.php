<?php
declare(strict_types=1);

namespace App\Modules\User\Domain;

use App\Modules\Shared\Domain\ValueObject\Email;
use App\Modules\Shared\Domain\ValueObject\EntityId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class User
{
    private string $id;

    private string $email;

    private ?string $password;

    private ?string $firstName;

    private ?string $lastName;

    private array $roles = [];

    private Collection $authTokens;

    private \DateTime $createdAt;

    public function __construct(
        EntityId $id, Email $email, string $password, ?string $firstName, ?string $lastName, array $roles,
        \DateTime $createdAt
    ) {
        $this->id = $id->getValue();
        $this->email = $email->getValue();
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = $roles;
        $this->createdAt = $createdAt;

        $this->authTokens = new ArrayCollection();
    }

    public static function create(
        string $email, string $password, ?string $firstName, ?string $lastName, array $roles = []
    ): User {
        return new self(EntityId::create(), new Email($email), $password, $firstName, $lastName, $roles,
            new \DateTime());
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(Email $email): User
    {
        $this->email = $email->getValue();

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): User
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): User
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Guarantee every user at least has ROLE_USER.
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getRole(): string
    {
        return $this->getRoles()[0];
    }

    public function getAuthTokens(): Collection
    {
        return $this->authTokens;
    }

    public function addAuthToken(AuthToken $authToken): self
    {
        $this->authTokens->add($authToken);

        return $this;
    }

    public function removeAuthToken(AuthToken $authToken): self
    {
        $this->authTokens->removeElement($authToken);

        return $this;
    }

    public function removeAllAuthTokens(): self
    {
        $this->authTokens = new ArrayCollection();

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
