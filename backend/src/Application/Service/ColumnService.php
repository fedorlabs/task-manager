<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Column;
use App\Domain\Repository\ColumnRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function getColumnById(int $id): Column
    {
        $column = $this->columnRepository->findById($id);
        if (!$column) {
            throw new NotFoundHttpException('Column not found');
        }
        return $column;
    }

    public function getColumnCount(): int
    {
        return $this->columnRepository->count();
    }

    public function createColumn(string $title): Column
    {
        $column = new Column();
        $column->setTitle($title);
        $this->columnRepository->save($column);
        return $column;
    }

    public function updateColumn(int $id, array $data): Column
    {
        $column = $this->getColumnById($id);
        if (array_key_exists('title', $data)) {
            $column->setTitle($data['title']);
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
