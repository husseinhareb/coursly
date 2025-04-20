<?php
// src/Entity/AdminAlert.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdminAlertRepository;

#[ORM\Entity(repositoryClass: AdminAlertRepository::class)]
#[ORM\Table(name: 'admin_alert')]
class AdminAlert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'alerts')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\ManyToOne(targetEntity: Post::class)]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Post $post = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $admin;

    #[ORM\Column(type:'string', length:20)]
    private string $action; // 'created','updated','deleted'

    #[ORM\Column(type:'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(Course $course, User $admin, string $action, ?Post $post = null)
    {
        $this->course    = $course;
        $this->admin     = $admin;
        $this->action    = $action;
        $this->post      = $post;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }
    public function setCourse(Course $c): self
    {
        $this->course = $c;
        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }
    public function setPost(?Post $p): self
    {
        $this->post = $p;
        return $this;
    }

    public function getAdmin(): User
    {
        return $this->admin;
    }
    public function setAdmin(User $u): self
    {
        $this->admin = $u;
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
}
