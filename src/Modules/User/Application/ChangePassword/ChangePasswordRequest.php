<?php
declare(strict_types=1);

namespace App\Modules\User\Application\ChangePassword;

use App\Modules\Shared\Application\Contract\RequestInterface;

class ChangePasswordRequest implements RequestInterface
{
    public string $id;

    public string $currentPassword;

    public string $password;

    public string $passwordConfirmation;

    public function __construct(string $id, string $currentPassword, string $password, string $passwordConfirmation) {
        $this->id = $id;
        $this->currentPassword = $currentPassword;
        $this->password = $password;
        $this->passwordConfirmation = $passwordConfirmation;
    }
}
