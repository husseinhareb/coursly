<?php
// src/Entity/Post.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'post')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\Column(type:'string', length:255)]
    private string $title;

    #[ORM\Column(type:'text')]
    private string $content;

    #[ORM\Column(type:'string', length:50)]
    private string $type; // e.g. 'message' or 'file'

    #[ORM\Column(type:'string', length:255, nullable:true)]
    private ?string $filePath = null;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    // --- Pinning ---
    #[ORM\Column(type:'boolean')]
    private bool $isPinned = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'pinned_by_id', referencedColumnName: 'id', nullable: true)]
    private ?User $pinnedBy = null;

    #[ORM\Column(type:'datetime_immutable', nullable:true)]
    private ?\DateTimeImmutable $pinnedAt = null;

    // --- Custom ordering ---
    #[ORM\Column(type:'integer')]
    private int $position = 0;

    // --- Category (message types) ---
    #[ORM\ManyToOne(targetEntity: MessageCategory::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    private ?MessageCategory $category = null;

    // --- Relationships ---
    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    public function __construct(string $title, string $content, string $type, User $author, Course $course)
    {
        $this->title     = $title;
        $this->content   = $content;
        $this->type      = $type;
        $this->author    = $author;
        $this->course    = $course;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // --- Basic fields ---
    public function getTitle(): string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title     = $title;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }
    public function setContent(string $content): self
    {
        $this->content   = $content;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type      = $type;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }
    public function setFilePath(?string $path): self
    {
        $this->filePath  = $path;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    // --- Timestamps ---
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(\DateTimeImmutable $dt): self
    {
        $this->updatedAt = $dt;
        return $this;
    }

    // --- Pinning ---
    public function isPinned(): bool
    {
        return $this->isPinned;
    }
    public function setIsPinned(bool $flag, ?User $by = null): self
    {
        $this->isPinned = $flag;
        $this->pinnedBy = $by;
        $this->pinnedAt = $flag ? new \DateTimeImmutable() : null;
        return $this;
    }

    public function getPinnedBy(): ?User
    {
        return $this->pinnedBy;
    }
    public function getPinnedAt(): ?\DateTimeImmutable
    {
        return $this->pinnedAt;
    }

    // --- Ordering ---
    public function getPosition(): int
    {
        return $this->position;
    }
    public function setPosition(int $pos): self
    {
        $this->position = $pos;
        return $this;
    }

    // --- Category ---
    public function getCategory(): ?MessageCategory
    {
        return $this->category;
    }
    public function setCategory(?MessageCategory $cat): self
    {
        $this->category = $cat;
        return $this;
    }

    // --- Relations ---
    public function getCourse(): Course
    {
        return $this->course;
    }
    public function setCourse(Course $c): self
    {
        $this->course = $c;
        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }
    public function setAuthor(User $u): self
    {
        $this->author = $u;
        return $this;
    }
}
