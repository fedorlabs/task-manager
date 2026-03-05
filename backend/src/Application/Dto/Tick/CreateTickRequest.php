<?php

declare(strict_types=1);

namespace App\Application\Dto\Tick;

use Symfony\Component\Validator\Constraints as Assert;

class CreateTickRequest
{
    private bool $doneProvided = false;

    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    public string $text;

    public ?bool $done = null;

    #[Assert\Positive]
    public ?int $taskId = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->text = trim((string) ($data['text'] ?? ''));
        if (array_key_exists('done', $data)) {
            $dto->doneProvided = true;
            $dto->done = self::parseBoolOrNull($data['done']);
        }
        $dto->taskId = self::parseIntOrInvalid($data['taskId'] ?? null);
        return $dto;
    }

    public function isDoneProvided(): bool
    {
        return $this->doneProvided;
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
