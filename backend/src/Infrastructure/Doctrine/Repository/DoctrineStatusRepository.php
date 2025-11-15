<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Status;
use App\Domain\Repository\StatusRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineStatusRepository extends ServiceEntityRepository implements StatusRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function findById(int $id): ?Status
    {
        return $this->find($id);
    }

    /** @return Status[] */
    public function findAll(): array
    {
        return parent::findAll();
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    public function save(Status $status): void
    {
        $this->getEntityManager()->persist($status);
        $this->getEntityManager()->flush();
    }

    public function remove(Status $status): void
    {
        $this->getEntityManager()->remove($status);
        $this->getEntityManager()->flush();
    }
}
