<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\Query\TickQuery;
use App\Application\Dto\Tick\CreateTickRequest;
use App\Application\Dto\Tick\UpdateTickRequest;
use App\Domain\Entity\Tick;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\TickRepositoryInterface;

class TickService
{
    public function __construct(
        private readonly TickRepositoryInterface $tickRepository,
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    /** @return Tick[] */
    public function getTicks(TickQuery $query): array
    {
        if ($query->taskId !== null && !$this->taskRepository->findById($query->taskId)) {
            throw new NotFoundException('Task not found');
        }
        return $this->tickRepository->findByFilters(
            $query->taskId,
            $query->sort,
            $query->order ?? 'asc',
            $query->limit,
            $query->offset
        );
    }

    public function getTickById(int $id): Tick
    {
        $tick = $this->tickRepository->findById($id);
        if (!$tick) {
            throw new NotFoundException('Tick not found');
        }
        return $tick;
    }

    /** @return Tick[] */
    public function getTicksByTaskId(TickQuery $query): array
    {
        if ($query->taskId === null) {
            throw new ValidationException('Task is required');
        }
        if (!$this->taskRepository->findById($query->taskId)) {
            throw new NotFoundException('Task not found');
        }
        return $this->tickRepository->findByFilters(
            $query->taskId,
            $query->sort,
            $query->order ?? 'asc',
            $query->limit,
            $query->offset
        );
    }

    public function getTickCount(TickQuery $query): int
    {
        if ($query->taskId !== null && !$this->taskRepository->findById($query->taskId)) {
            throw new NotFoundException('Task not found');
        }
        return $this->tickRepository->countByFilters($query->taskId);
    }

    public function createTick(CreateTickRequest $dto): Tick
    {
        $tick = new Tick();
        $this->hydrateTick($tick, $dto, true);
        $this->tickRepository->save($tick);
        return $tick;
    }

    public function updateTick(int $id, UpdateTickRequest $dto): Tick
    {
        $tick = $this->getTickById($id);
        $this->hydrateTick($tick, $dto, false);
        $this->tickRepository->save($tick);
        return $tick;
    }

    public function deleteTick(int $id): void
    {
        $tick = $this->getTickById($id);
        $this->tickRepository->remove($tick);
    }

    private function hydrateTick(Tick $tick, CreateTickRequest|UpdateTickRequest $dto, bool $isCreate): void
    {
        if ($isCreate) {
            $tick->setText($dto->text);
        } elseif ($dto instanceof UpdateTickRequest && $dto->isProvided('text')) {
            $tick->setText($dto->text ?? '');
        }

        if ($dto instanceof UpdateTickRequest) {
            if ($dto->isProvided('done')) {
                if ($dto->done === null) {
                    throw new ValidationException('Done must be a boolean');
                }
                $tick->setDone($dto->done);
            }
            if ($dto->isProvided('taskId')) {
                if ($dto->taskId === null) {
                    $tick->setTask(null);
                } else {
                    if ($dto->taskId === -1) {
                        throw new ValidationException('Task id must be an integer');
                    }
                    $task = $this->taskRepository->findById($dto->taskId);
                    if (!$task) {
                        throw new NotFoundException('Task not found');
                    }
                    $tick->setTask($task);
                }
            }
        } else {
            if ($dto->isDoneProvided()) {
                if ($dto->done === null) {
                    throw new ValidationException('Done must be a boolean');
                }
                $tick->setDone($dto->done);
            }
            if ($dto->taskId === null) {
                throw new ValidationException('Task is required');
            }
            if ($dto->taskId === -1) {
                throw new ValidationException('Task id must be an integer');
            }
            $task = $this->taskRepository->findById($dto->taskId);
            if (!$task) {
                throw new NotFoundException('Task not found');
            }
            $tick->setTask($task);
        }
    }
}
