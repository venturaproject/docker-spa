<?php
declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject;

class Email
{
    private string $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email {$value} is not valid.");
        }
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
