<?php
// src/Entity/Post.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;
use App\Entity\Course;
use App\Entity\User;
use App\Entity\MessageCategory;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'post')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    // — Pin/unpin support
    #[ORM\Column(type: 'boolean')]
    private bool $isPinned = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'pinned_by_id', referencedColumnName: 'id', nullable: true)]
    private ?User $pinnedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $pinnedAt = null;

    // — Tri personnalisé
    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    // — Recherche de catégorie
    #[ORM\ManyToOne(targetEntity: MessageCategory::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: true)]
    private ?MessageCategory $category = null;

    // — Relations
    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    // — Titre
    public function getTitle(): ?string
    {
        return $this->title;
    }
    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->touch();
        return $this;
    }

    // — Contenu
    public function getContent(): ?string
    {
        return $this->content;
    }
    public function setContent(string $content): self
    {
        $this->content = $content;
        $this->touch();
        return $this;
    }

    // — Type (message ou fichier)
    public function getType(): ?string
    {
        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type = $type;
        $this->touch();
        return $this;
    }

    // — Chemin du fichier
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }
    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        $this->touch();
        return $this;
    }

    // — Horodatages
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

    // — Épinglez/Désépingler
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

    // — Tri personnalisé
    public function getPosition(): int
    {
        return $this->position;
    }
    public function setPosition(int $pos): self
    {
        $this->position = $pos;
        return $this;
    }

    // — Categorie
    public function getCategory(): ?MessageCategory
    {
        return $this->category;
    }
    public function setCategory(?MessageCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    // — relation entre les cours
    public function getCourse(): Course
    {
        return $this->course;
    }
    public function setCourse(Course $course): self
    {
        $this->course = $course;
        return $this;
    }

    // — relations entre les auteurs
    public function getAuthor(): User
    {
        return $this->author;
    }
    public function setAuthor(User $author): self
    {
        $this->author = $author;
        return $this;
    }

    // — Fonction utilitaire interne pour mettre à jour updatedAt
    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
