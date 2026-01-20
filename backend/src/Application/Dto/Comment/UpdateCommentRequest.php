<?php

declare(strict_types=1);

namespace App\Application\Dto\Comment;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCommentRequest
{
    #[Assert\Length(min: 1, max: 2000)]
    public ?string $text = null;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        if (array_key_exists('text', $data)) {
            $dto->text = trim((string) $data['text']);
        }
        return $dto;
    }
}
