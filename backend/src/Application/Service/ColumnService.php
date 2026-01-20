<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\Column\CreateColumnRequest;
use App\Application\Dto\Column\UpdateColumnRequest;
use App\Application\Dto\Query\ColumnQuery;
use App\Domain\Entity\Column;
use App\Domain\Exception\ValidationException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Repository\ColumnRepositoryInterface;

class ColumnService
{
    public function __construct(
        private readonly ColumnRepositoryInterface $columnRepository,
    ) {}

    /** @return Column[] */
    public function getAllColumns(): array
    {
        return $this->columnRepository->findAll();
    }

    /** @return Column[] */
    public function getColumns(ColumnQuery $query): array
    {
        return $this->columnRepository->findPaginated($query->limit, $query->offset, $query->sort, $query->order ?? 'asc');
    }

    public function getColumnById(int $id): Column
    {
        $column = $this->columnRepository->findById($id);
        if (!$column) {
            throw new NotFoundException('Column not found');
        }
        return $column;
    }

    public function getColumnCount(): int
    {
        return $this->columnRepository->count();
    }

    public function createColumn(CreateColumnRequest $dto): Column
    {
        $column = new Column();
        $column->setTitle($dto->title);
        $this->columnRepository->save($column);
        return $column;
    }

    public function updateColumn(int $id, UpdateColumnRequest $dto): Column
    {
        $column = $this->getColumnById($id);
        if ($dto->isProvided('title')) {
            if ($dto->title === null) {
                throw new ValidationException('Title is required');
            }
            $column->setTitle($dto->title);
        }
        $this->columnRepository->save($column);
        return $column;
    }

    public function deleteColumn(int $id): void
    {
        $column = $this->getColumnById($id);
        $this->columnRepository->remove($column);
    }
}
