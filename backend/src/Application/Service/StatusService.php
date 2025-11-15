<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Status;
use App\Domain\Repository\StatusRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function getStatusById(int $id): Status
    {
        $status = $this->statusRepository->findById($id);
        if (!$status) {
            throw new NotFoundHttpException('Status not found');
        }
        return $status;
    }

    public function getStatusCount(): int
    {
        return $this->statusRepository->count();
    }

    public function createStatus(string $name): Status
    {
        $status = new Status();
        $status->setName($name);
        $this->statusRepository->save($status);
        return $status;
    }

    public function updateStatus(int $id, array $data): Status
    {
        $status = $this->getStatusById($id);
        if (array_key_exists('name', $data)) {
            $status->setName($data['name']);
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
