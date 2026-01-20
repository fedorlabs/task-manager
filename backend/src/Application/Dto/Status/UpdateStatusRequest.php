<?php

declare(strict_types=1);

namespace App\Application\Dto\Status;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateStatusRequest
{
    /** @var array<string, bool> */
    private array $present = [];

    #[Assert\Length(min: 1, max: 255)]
    public ?string $name = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        if (array_key_exists('name', $data)) {
            $dto->present['name'] = true;
            $dto->name = trim((string) $data['name']);
        }
        return $dto;
    }

    public function isProvided(string $field): bool
    {
        return $this->present[$field] ?? false;
    }
}
