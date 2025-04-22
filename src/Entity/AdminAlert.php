<?php
// src/Entity/AdminAlert.php

namespace App\Entity;

use App\Repository\AdminAlertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminAlertRepository::class)]
#[ORM\Table(name: 'admin_alert')]
class AdminAlert
{
    // ────────────────────────────────
    // Core columns
    // ────────────────────────────────
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'alerts')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(
        name: 'post_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    private ?Post $post = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $admin;

    /**
     * `'created' | 'updated' | 'deleted'`
     */
    #[ORM\Column(type: 'string', length: 20)]
    private string $action;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    // ────────────────────────────────
    // Acknowledgements (per-professeur)
    // ────────────────────────────────
    /**
     * @var Collection<int, AlertAcknowledgement>
     */
    #[ORM\OneToMany(
        mappedBy: 'alert',
        targetEntity: AlertAcknowledgement::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $acknowledgements;

    // ────────────────────────────────
    // Constructor
    // ────────────────────────────────
    public function __construct(
        Course $course,
        User   $admin,
        string $action,
        ?Post  $post = null
    ) {
        $this->course          = $course;
        $this->admin           = $admin;
        $this->action          = $action;
        $this->post            = $post;
        $this->createdAt       = new \DateTimeImmutable();
        $this->acknowledgements = new ArrayCollection();
    }

    // ────────────────────────────────
    // Helpers
    // ────────────────────────────────
    /**
     * True si l’utilisateur a déjà cliqué « J’ai compris ».
     */
    public function isAcknowledgedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->acknowledgements->exists(
            fn ($k, AlertAcknowledgement $ack) => $ack->getUser() === $user
        );
    }

    // ────────────────────────────────
    // Getters / setters (fluent where pertinent)
    // ────────────────────────────────
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }
    public function setCourse(Course $course): self
    {
        $this->course = $course;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }
    public function setPost(?Post $post): self
    {
        $this->post = $post;
        return $this;
    }

    public function getAdmin(): User
    {
        return $this->admin;
    }
    public function setAdmin(User $admin): self
    {
        $this->admin = $admin;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }
    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, AlertAcknowledgement>
     */
    public function getAcknowledgements(): Collection
    {
        return $this->acknowledgements;
    }
    public function addAcknowledgement(AlertAcknowledgement $ack): self
    {
        if (!$this->acknowledgements->contains($ack)) {
            $this->acknowledgements->add($ack);
        }
        return $this;
    }
    public function removeAcknowledgement(AlertAcknowledgement $ack): self
    {
        $this->acknowledgements->removeElement($ack);
        return $this;
    }
}
