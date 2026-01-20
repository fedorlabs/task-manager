<?php

declare(strict_types=1);

namespace App\Application\Dto\Column;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateColumnRequest
{
    /** @var array<string, bool> */
    private array $present = [];

    #[Assert\Length(min: 1, max: 255)]
    public ?string $title = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        if (array_key_exists('title', $data)) {
            $dto->present['title'] = true;
            $dto->title = trim((string) $data['title']);
        }
        return $dto;
    }

    public function isProvided(string $field): bool
    {
        return $this->present[$field] ?? false;
    }
}
