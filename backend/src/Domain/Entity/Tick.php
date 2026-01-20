<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'ticks')]
#[OA\Schema(title: 'Tick', description: 'Подзадача (чекбокс)')]
class Tick
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['tick:read'])]
    #[OA\Property(description: 'ID подзадачи', example: 1)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['tick:read'])]
    #[OA\Property(description: 'Текст подзадачи', example: 'Настроить JWT')]
    private string $text;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['tick:read'])]
    #[OA\Property(description: 'Выполнена', example: false)]
    private bool $done;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'ticks')]
    #[ORM\JoinColumn(name: 'task_id', nullable: true, onDelete: 'CASCADE')]
    #[Groups(['tick:read'])]
    #[OA\Property(ref: '#/components/schemas/Task')]
    private ?Task $task = null;

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

    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;
        return $this;
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
}
