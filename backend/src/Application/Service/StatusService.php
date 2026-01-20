<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\Dto\Query\StatusQuery;
use App\Application\Dto\Status\CreateStatusRequest;
use App\Application\Dto\Status\UpdateStatusRequest;
use App\Domain\Entity\Status;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\ValidationException;
use App\Domain\Repository\StatusRepositoryInterface;

class StatusService
{
    public function __construct(
        private readonly StatusRepositoryInterface $statusRepository,
    ) {}

    /** @return Status[] */
    public function getAllStatuses(): array
    {
        return $this->statusRepository->findAll();
    }

    /** @return Status[] */
    public function getStatuses(StatusQuery $query): array
    {
        return $this->statusRepository->findPaginated($query->limit, $query->offset, $query->sort, $query->order ?? 'asc');
    }

    public function getStatusById(int $id): Status
    {
        $status = $this->statusRepository->findById($id);
        if (!$status) {
            throw new NotFoundException('Status not found');
        }
        return $status;
    }

    public function getStatusCount(): int
    {
        return $this->statusRepository->count();
    }

    public function createStatus(CreateStatusRequest $dto): Status
    {
        $status = new Status();
        $status->setName($dto->name);
        $this->statusRepository->save($status);
        return $status;
    }

    public function updateStatus(int $id, UpdateStatusRequest $dto): Status
    {
        $status = $this->getStatusById($id);
        if ($dto->isProvided('name')) {
            if ($dto->name === null) {
                throw new ValidationException('Name is required');
            }
            $status->setName($dto->name);
        }
        $this->statusRepository->save($status);
        return $status;
    }

    public function deleteStatus(int $id): void
    {
        $status = $this->getStatusById($id);
        $this->statusRepository->remove($status);
    }
}
