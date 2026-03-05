<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

trait RepositoryOrderTrait
{
    private function normalizeOrder(string $order): string
    {
        return strtolower($order) === 'desc' ? 'DESC' : 'ASC';
    }
}
