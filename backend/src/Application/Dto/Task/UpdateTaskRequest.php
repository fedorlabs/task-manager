<?php

declare(strict_types=1);

namespace App\Application\Dto\Task;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTaskRequest
{
    /** @var array<string, bool> */
    private array $present = [];

    #[Assert\Length(min: 1, max: 255)]
    public ?string $title = null;

    #[Assert\Length(max: 5000)]
    public ?string $description = null;

    #[Assert\PositiveOrZero]
    public ?int $sortOrder = null;

    #[Assert\DateTime]
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
        if (array_key_exists('title', $data)) {
            $dto->present['title'] = true;
            $dto->title = trim((string) $data['title']);
        }
        if (array_key_exists('description', $data)) {
            $dto->present['description'] = true;
            $dto->description = isset($data['description']) ? (string) $data['description'] : null;
        }
        if (array_key_exists('sortOrder', $data)) {
            $dto->present['sortOrder'] = true;
            $dto->sortOrder = self::parseIntOrInvalid($data['sortOrder']);
        }
        if (array_key_exists('dueDate', $data)) {
            $dto->present['dueDate'] = true;
            $dto->dueDate = isset($data['dueDate']) ? (string) $data['dueDate'] : null;
        }
        if (array_key_exists('url', $data)) {
            $dto->present['url'] = true;
            $dto->url = isset($data['url']) ? (string) $data['url'] : null;
        }
        if (array_key_exists('urlDescription', $data)) {
            $dto->present['urlDescription'] = true;
            $dto->urlDescription = isset($data['urlDescription']) ? (string) $data['urlDescription'] : null;
        }
        if (array_key_exists('tags', $data)) {
            $dto->present['tags'] = true;
            $dto->tags = isset($data['tags']) ? (string) $data['tags'] : null;
        }
        if (array_key_exists('columnId', $data)) {
            $dto->present['columnId'] = true;
            $dto->columnId = self::parseIntOrInvalid($data['columnId']);
        }
        if (array_key_exists('statusId', $data)) {
            $dto->present['statusId'] = true;
            $dto->statusId = self::parseIntOrInvalid($data['statusId']);
        }
        return $dto;
    }

    public function isProvided(string $field): bool
    {
        return $this->present[$field] ?? false;
    }

    private static function parseIntOrInvalid(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        return $validated === false ? -1 : (int) $validated;
    }
}
