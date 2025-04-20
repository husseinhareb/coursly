<?php
// src/Entity/Course.php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Repository\CourseRepository;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
#[ORM\Table(name: 'course')]
#[UniqueEntity(fields: ['code'], message: 'This course code already exists.')]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    private string $description;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $code;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $background = null;

    /** @var Collection<int, Enrollment> */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Enrollment::class, cascade: ['persist', 'remove'])]
    private Collection $enrollments;

    /** @var Collection<int, Post> */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Post::class, cascade: ['persist', 'remove'])]
    private Collection $posts;

    /** @var Collection<int, AdminAlert> */
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: AdminAlert::class, cascade: ['persist', 'remove'])]
    private Collection $alerts;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->posts       = new ArrayCollection();
        $this->alerts      = new ArrayCollection();
        $this->createdAt   = new \DateTimeImmutable();
        $this->updatedAt   = new \DateTimeImmutable();
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
        $this->title     = $title;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        $this->updatedAt   = new \DateTimeImmutable();
        return $this;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code      = strtoupper($code);
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getBackground(): ?string
    {
        return $this->background;
    }

    public function setBackground(?string $background): self
    {
        $this->background = $background;
        $this->updatedAt  = new \DateTimeImmutable();
        return $this;
    }

    /**
     * @return Collection<int, Enrollment>
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function addEnrollment(Enrollment $enrollment): self
    {
        if (!$this->enrollments->contains($enrollment)) {
            $this->enrollments->add($enrollment);
            $enrollment->setCourse($this);
        }
        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): self
    {
        if ($this->enrollments->removeElement($enrollment)) {
            if ($enrollment->getCourse() === $this) {
                $enrollment->setCourse(null);
            }
        }
        return $this;
    }

    /**
     * Convenience: get all User entities enrolled in this course
     *
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->enrollments->map(fn(Enrollment $e) => $e->getUser());
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setCourse($this);
        }
        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getCourse() === $this) {
                $post->setCourse(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, AdminAlert>
     */
    public function getAlerts(): Collection
    {
        return $this->alerts;
    }

    public function addAlert(AdminAlert $alert): self
    {
        if (!$this->alerts->contains($alert)) {
            $this->alerts[] = $alert;
            $alert->setCourse($this);
        }
        return $this;
    }

    public function removeAlert(AdminAlert $alert): self
    {
        if ($this->alerts->removeElement($alert)) {
            if ($alert->getCourse() === $this) {
                $alert->setCourse(null);
            }
        }
        return $this;
    }
}
