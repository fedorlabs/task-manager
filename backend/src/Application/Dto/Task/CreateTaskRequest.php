<?php

declare(strict_types=1);

namespace App\Application\Dto\Task;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTaskRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    #[Assert\Length(max: 5000)]
    public ?string $description = null;

    #[Assert\PositiveOrZero]
    public ?int $sortOrder = null;

    #[Assert\DateTime(format: 'Y-m-d\TH:i:s.vP')]
    public ?string $dueDate = null;

    #[Assert\Url]
    #[Assert\Length(max: 500)]
    public ?string $url = null;

    #[Assert\Length(max: 500)]
    public ?string $urlDescription = null;

    #[Assert\Length(max: 1000)]
    public ?string $tags = null;

    #[Assert\Positive]
    public ?int $columnId = null;

    #[Assert\Positive]
    public ?int $statusId = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->title = trim((string) ($data['title'] ?? ''));
        $dto->description = isset($data['description']) ? (string) $data['description'] : null;
        $dto->sortOrder = self::parseIntOrInvalid($data['sortOrder'] ?? null);
        $dto->dueDate = isset($data['dueDate']) && $data['dueDate'] !== '' ? (string) $data['dueDate'] : null;
        $dto->url = isset($data['url']) && $data['url'] !== '' ? (string) $data['url'] : null;
        $dto->urlDescription = isset($data['urlDescription']) ? (string) $data['urlDescription'] : null;
        $dto->tags = isset($data['tags']) ? (string) $data['tags'] : null;
        $dto->columnId = self::parseIntOrInvalid($data['columnId'] ?? null);
        $dto->statusId = self::parseIntOrInvalid($data['statusId'] ?? null);
        return $dto;
    }

    private static function parseIntOrInvalid(string|int|float|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        return $validated === false ? -1 : (int) $validated;
    }
}
