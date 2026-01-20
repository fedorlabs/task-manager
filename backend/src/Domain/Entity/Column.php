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
#[ORM\Table(name: 'columns')]
#[OA\Schema(title: 'Column', description: 'Колонка Kanban-доски')]
class Column
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['column:read', 'task:read', 'comment:read', 'tick:read'])]
    #[OA\Property(description: 'ID колонки', example: 1)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['column:read', 'task:read', 'comment:read', 'tick:read'])]
    #[OA\Property(description: 'Название колонки', example: 'В работе')]
    private string $title;

    /** @var Collection<int, Task> */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'column')]
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /** @return Collection<int, Task> */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }
}
