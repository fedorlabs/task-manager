<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'comments')]
#[ORM\HasLifecycleCallbacks]
#[OA\Schema(title: 'Comment', description: 'Комментарий к задаче')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['comment:read'])]
    #[OA\Property(description: 'ID комментария', example: 1)]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Groups(['comment:read'])]
    #[OA\Property(description: 'Текст комментария', example: 'Отличная работа!')]
    private string $text;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['comment:read'])]
    #[OA\Property(description: 'Дата создания', format: 'date-time')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['comment:read'])]
    #[OA\Property(description: 'Дата обновления', format: 'date-time')]
    private \DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'task_id', nullable: true, onDelete: 'CASCADE')]
    #[Groups(['comment:read'])]
    #[OA\Property(ref: '#/components/schemas/Task')]
    private ?Task $task = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[Groups(['comment:read'])]
    #[OA\Property(ref: '#/components/schemas/User')]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
