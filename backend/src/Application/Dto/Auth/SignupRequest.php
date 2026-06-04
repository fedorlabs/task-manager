<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use Symfony\Component\Validator\Constraints as Assert;

class SignupRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    public string $name;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 255)]
    public string $password;

    #[Assert\Length(max: 255)]
    public ?string $avatar = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->name = trim((string) ($data['name'] ?? ''));
        $dto->email = trim((string) ($data['email'] ?? ''));
        $dto->password = (string) ($data['password'] ?? '');
        $dto->avatar = isset($data['avatar']) ? (string) $data['avatar'] : null;
        return $dto;
    }
}
