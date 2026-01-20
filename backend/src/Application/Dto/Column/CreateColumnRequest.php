<?php

declare(strict_types=1);

namespace App\Application\Dto\Column;

use Symfony\Component\Validator\Constraints as Assert;

class CreateColumnRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->title = trim((string) ($data['title'] ?? ''));
        return $dto;
    }
}
