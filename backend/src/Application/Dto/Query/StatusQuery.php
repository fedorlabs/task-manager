<?php

declare(strict_types=1);

namespace App\Application\Dto\Query;

use App\Application\Service\QueryParamResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class StatusQuery
{
    #[Assert\Range(min: 1, max: 200)]
    public int $limit = 100;

    #[Assert\Range(min: 0)]
    public int $offset = 0;

    #[Assert\Choice(choices: ['id', 'name'])]
    public ?string $sort = null;

    #[Assert\Choice(choices: ['asc', 'desc'])]
    public ?string $order = null;

    public static function fromRequest(Request $request, QueryParamResolver $resolver): self
    {
        $dto = new self();
        $dto->limit = $resolver->getLimit($request);
        $dto->offset = $resolver->getOffset($request);
        $dto->sort = self::normalizeString($request->query->get('sort'));
        $dto->order = self::normalizeOrder($request->query->get('order'), $dto->sort);
        return $dto;
    }

    private static function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private static function normalizeOrder(mixed $value, ?string $sort): ?string
    {
        if ($value === null || $value === '') {
            return $sort ? 'asc' : null;
        }
        return strtolower((string) $value);
    }
}
