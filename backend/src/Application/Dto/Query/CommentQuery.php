<?php

declare(strict_types=1);

namespace App\Application\Dto\Query;

use App\Application\Service\QueryParamResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class CommentQuery
{
    #[Assert\Range(min: 1, max: 200)]
    public int $limit = 100;

    #[Assert\Range(min: 0)]
    public int $offset = 0;

    #[Assert\Positive]
    public ?int $taskId = null;

    #[Assert\Choice(choices: ['id', 'createdAt', 'updatedAt'])]
    public ?string $sort = null;

    #[Assert\Choice(choices: ['asc', 'desc'])]
    public ?string $order = null;

    #[Assert\Length(max: 100)]
    public ?string $q = null;

    public static function fromRequest(Request $request, QueryParamResolver $resolver): self
    {
        $dto = new self();
        $dto->limit = $resolver->getLimit($request);
        $dto->offset = $resolver->getOffset($request);
        $dto->taskId = $resolver->getOptionalPositiveInt($request, 'taskId');
        $dto->sort = self::normalizeString($request->query->get('sort'));
        $dto->order = self::normalizeOrder($request->query->get('order'), $dto->sort);
        $dto->q = self::normalizeString($request->query->get('q'));
        return $dto;
    }

    private static function normalizeString(string|int|float|null $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private static function normalizeOrder(string|int|float|null $value, ?string $sort): ?string
    {
        if ($value === null || $value === '') {
            return $sort ? 'asc' : null;
        }
        return strtolower((string) $value);
    }
}
