<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\User;
use App\Domain\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<User> */
class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findById(string $id): ?User
    {
        return $this->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /** @return User[] */
    public function findAll(): array
    {
        return parent::findAll();
    }

    /** @return User[] */
    public function findPaginated(int $limit, int $offset, ?string $sort, string $order): array
    {
        $sortField = match ($sort) {
            'name' => 'u.name',
            'email' => 'u.email',
            default => 'u.id',
        };

        return $this->createQueryBuilder('u')
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

    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function remove(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
