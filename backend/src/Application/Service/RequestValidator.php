<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {}

    public function validate(object $dto): void
    {
        $violations = $this->validator->validate($dto);
        if (count($violations) === 0) {
            return;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => (string) $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }

        throw new ValidationException('Validation failed', $errors);
    }
}
