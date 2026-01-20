<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity]
#[ORM\Table(name: 'statuses')]
#[OA\Schema(title: 'Status', description: 'Статус задачи')]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['status:read', 'task:read', 'comment:read', 'tick:read'])]
    #[OA\Property(description: 'ID статуса', example: 1)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['status:read', 'task:read', 'comment:read', 'tick:read'])]
    #[OA\Property(description: 'Название статуса', example: 'Выполнено')]
    private string $name;

    /** @var Collection<int, Task> */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'status')]
    #[Ignore]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /** @return Collection<int, Task> */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }
}
