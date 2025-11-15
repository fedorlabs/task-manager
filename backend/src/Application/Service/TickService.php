<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Tick;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\Repository\TickRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TickService
{
    public function __construct(
        private readonly TickRepositoryInterface $tickRepository,
        private readonly TaskRepositoryInterface $taskRepository,
    ) {}

    /** @return Tick[] */
    public function getAllTicks(): array
    {
        return $this->tickRepository->findAll();
    }

    public function getTickById(int $id): Tick
    {
        $tick = $this->tickRepository->findById($id);
        if (!$tick) {
            throw new NotFoundHttpException('Tick not found');
        }
        return $tick;
    }

    /** @return Tick[] */
    public function getTicksByTaskId(int $taskId): array
    {
        return $this->tickRepository->findByTaskId($taskId);
    }

    public function getTickCount(): int
    {
        return $this->tickRepository->count();
    }

    public function createTick(array $data): Tick
    {
        $tick = new Tick();
        $this->hydrateTick($tick, $data);
        $this->tickRepository->save($tick);
        return $tick;
    }

    public function updateTick(int $id, array $data): Tick
    {
        $tick = $this->getTickById($id);
        $this->hydrateTick($tick, $data);
        $this->tickRepository->save($tick);
        return $tick;
    }

    public function deleteTick(int $id): void
    {
        $tick = $this->getTickById($id);
        $this->tickRepository->remove($tick);
    }

    private function hydrateTick(Tick $tick, array $data): void
    {
        if (array_key_exists('text', $data)) {
            $tick->setText($data['text']);
        }
        if (array_key_exists('done', $data)) {
            $tick->setDone((bool) $data['done']);
        }
        if (array_key_exists('taskId', $data)) {
            $tick->setTask($data['taskId'] ? $this->taskRepository->findById((int) $data['taskId']) : null);
        }
    }
}
