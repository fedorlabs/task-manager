<?php

declare(strict_types=1);

namespace App\Application\Dto\Task;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTaskRequest
{
    public const FIELD_TITLE = 'title';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_SORT_ORDER = 'sortOrder';
    public const FIELD_DUE_DATE = 'dueDate';
    public const FIELD_URL = 'url';
    public const FIELD_URL_DESCRIPTION = 'urlDescription';
    public const FIELD_TAGS = 'tags';
    public const FIELD_COLUMN_ID = 'columnId';
    public const FIELD_STATUS_ID = 'statusId';

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
        if (array_key_exists(self::FIELD_TITLE, $data)) {
            $dto->present[self::FIELD_TITLE] = true;
            $dto->title = trim((string) $data[self::FIELD_TITLE]);
        }
        if (array_key_exists(self::FIELD_DESCRIPTION, $data)) {
            $dto->present[self::FIELD_DESCRIPTION] = true;
            $dto->description = isset($data[self::FIELD_DESCRIPTION]) ? (string) $data[self::FIELD_DESCRIPTION] : null;
        }
        if (array_key_exists(self::FIELD_SORT_ORDER, $data)) {
            $dto->present[self::FIELD_SORT_ORDER] = true;
            $dto->sortOrder = self::parseIntOrInvalid($data[self::FIELD_SORT_ORDER]);
        }
        if (array_key_exists(self::FIELD_DUE_DATE, $data)) {
            $dto->present[self::FIELD_DUE_DATE] = true;
            $dto->dueDate = isset($data[self::FIELD_DUE_DATE]) ? (string) $data[self::FIELD_DUE_DATE] : null;
        }
        if (array_key_exists(self::FIELD_URL, $data)) {
            $dto->present[self::FIELD_URL] = true;
            $dto->url = isset($data[self::FIELD_URL]) ? (string) $data[self::FIELD_URL] : null;
        }
        if (array_key_exists(self::FIELD_URL_DESCRIPTION, $data)) {
            $dto->present[self::FIELD_URL_DESCRIPTION] = true;
            $dto->urlDescription = isset($data[self::FIELD_URL_DESCRIPTION]) ? (string) $data[self::FIELD_URL_DESCRIPTION] : null;
        }
        if (array_key_exists(self::FIELD_TAGS, $data)) {
            $dto->present[self::FIELD_TAGS] = true;
            $dto->tags = isset($data[self::FIELD_TAGS]) ? (string) $data[self::FIELD_TAGS] : null;
        }
        if (array_key_exists(self::FIELD_COLUMN_ID, $data)) {
            $dto->present[self::FIELD_COLUMN_ID] = true;
            $dto->columnId = self::parseIntOrInvalid($data[self::FIELD_COLUMN_ID]);
        }
        if (array_key_exists(self::FIELD_STATUS_ID, $data)) {
            $dto->present[self::FIELD_STATUS_ID] = true;
            $dto->statusId = self::parseIntOrInvalid($data[self::FIELD_STATUS_ID]);
        }
        return $dto;
    }

    public function isProvided(string $field): bool
    {
        return $this->present[$field] ?? false;
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
