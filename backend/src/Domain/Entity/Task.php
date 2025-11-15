<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $urlDescription = null;

    #[ORM\Column(type: 'string', length: 1000, nullable: true)]
    private ?string $tags = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    #[ORM\ManyToOne(targetEntity: Column::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'column_id', nullable: true, onDelete: 'SET NULL')]
    private ?Column $column = null;

    #[ORM\ManyToOne(targetEntity: Status::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'status_id', nullable: true, onDelete: 'SET NULL')]
    private ?Status $status = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /** @var Collection<int, Comment> */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'task', cascade: ['remove'])]
    private Collection $comments;

    /** @var Collection<int, Tick> */
    #[ORM\OneToMany(targetEntity: Tick::class, mappedBy: 'task', cascade: ['remove'])]
    private Collection $ticks;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->comments = new ArrayCollection();
        $this->ticks = new ArrayCollection();
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getUrlDescription(): ?string
    {
        return $this->urlDescription;
    }

    public function setUrlDescription(?string $urlDescription): self
    {
        $this->urlDescription = $urlDescription;
        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;
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

    public function getColumn(): ?Column
    {
        return $this->column;
    }

    public function setColumn(?Column $column): self
    {
        $this->column = $column;
        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;
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

    /** @return Collection<int, Comment> */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /** @return Collection<int, Tick> */
    public function getTicks(): Collection
    {
        return $this->ticks;
    }
}
