<?php

declare(strict_types=1);

namespace App\Application\Dto\Status;

use Symfony\Component\Validator\Constraints as Assert;

class CreateStatusRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->name = trim((string) ($data['name'] ?? ''));
        return $dto;
    }
}
