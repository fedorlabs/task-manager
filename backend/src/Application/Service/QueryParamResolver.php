<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;

class QueryParamResolver
{
    public function getLimit(Request $request, int $default = 100, int $max = 200): int
    {
        $value = $request->query->get('limit');
        if ($value === null || $value === '') {
            return $default;
        }

        $limit = $this->validateInt($value, 'limit');
        if ($limit < 1 || $limit > $max) {
            throw new ValidationException("limit must be between 1 and {$max}");
        }
        return $limit;
    }

    public function getOffset(Request $request, int $default = 0): int
    {
        $value = $request->query->get('offset');
        if ($value === null || $value === '') {
            return $default;
        }

        $offset = $this->validateInt($value, 'offset');
        if ($offset < 0) {
            throw new ValidationException('offset must be 0 or greater');
        }
        return $offset;
    }

    public function getOptionalInt(Request $request, string $name): ?int
    {
        $value = $request->query->get($name);
        if ($value === null || $value === '') {
            return null;
        }

        return $this->validateInt($value, $name);
    }

    public function getOptionalPositiveInt(Request $request, string $name): ?int
    {
        $value = $this->getOptionalInt($request, $name);
        if ($value === null) {
            return null;
        }
        if ($value <= 0) {
            throw new ValidationException("{$name} must be a positive integer");
        }
        return $value;
    }

    private function validateInt(string|int|float|null $value, string $field): int
    {
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false) {
            throw new ValidationException("{$field} must be an integer");
        }
        return (int) $validated;
    }
}
