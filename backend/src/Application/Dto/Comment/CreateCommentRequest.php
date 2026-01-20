<?php

declare(strict_types=1);

namespace App\Application\Dto\Comment;

use Symfony\Component\Validator\Constraints as Assert;

class CreateCommentRequest
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    public string $text;

    #[Assert\Positive]
    public ?int $taskId = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->text = trim((string) ($data['text'] ?? ''));
        $dto->taskId = self::parseIntOrInvalid($data['taskId'] ?? null);
        return $dto;
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
