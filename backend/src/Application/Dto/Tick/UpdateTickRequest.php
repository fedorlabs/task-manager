<?php

declare(strict_types=1);

namespace App\Application\Dto\Tick;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTickRequest
{
    /** @var array<string, bool> */
    private array $present = [];

    #[Assert\Length(min: 1, max: 2000)]
    public ?string $text = null;

    public ?bool $done = null;

    #[Assert\Positive]
    public ?int $taskId = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        if (array_key_exists('text', $data)) {
            $dto->present['text'] = true;
            $dto->text = trim((string) $data['text']);
        }
        if (array_key_exists('done', $data)) {
            $dto->present['done'] = true;
            $dto->done = self::parseBoolOrNull($data['done']);
        }
        if (array_key_exists('taskId', $data)) {
            $dto->present['taskId'] = true;
            $dto->taskId = self::parseIntOrInvalid($data['taskId']);
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

    private static function parseBoolOrNull(string|int|float|bool|null $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
