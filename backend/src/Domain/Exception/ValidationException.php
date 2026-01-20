<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class ValidationException extends DomainException
{
    /** @var array<int, array{field: string, message: string}> */
    private array $errors;

    /**
     * @param array<int, array{field: string, message: string}> $errors
     */
    public function __construct(string $message, array $errors = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    /** @return array<int, array{field: string, message: string}> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
