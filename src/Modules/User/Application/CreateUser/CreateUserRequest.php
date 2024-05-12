<?php
declare(strict_types=1);

namespace App\Modules\User\Application\CreateUser;

use App\Modules\Shared\Application\Contract\RequestInterface;
use App\Modules\Shared\Domain\ValueObject\Email;

class CreateUserRequest implements RequestInterface
{
    public Email $email;

    public string $password;

    public string $firstName;

    public string $lastName;

    public array $roles;

    public function __construct(string $email, string $password, string $firstName, string $lastName, array $roles = [])
    {
        $this->email = new Email($email);
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->roles = $roles;
    }
}
