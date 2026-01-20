<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Status;
use App\Domain\Repository\StatusRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Status> */
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

    /** @return Status[] */
    public function findPaginated(int $limit, int $offset, ?string $sort, string $order): array
    {
        $sortField = match ($sort) {
            'name' => 's.name',
            default => 's.id',
        };

        return $this->createQueryBuilder('s')
            ->orderBy($sortField, $this->normalizeOrder($order))
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    private function normalizeOrder(string $order): string
    {
        return strtolower($order) === 'desc' ? 'DESC' : 'ASC';
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
