<?php
// src/Entity/Enrollment.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EnrollmentRepository;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ORM\Table(name: 'enrollments')]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }
    public function setUser(User $u): self
    {
        $this->user = $u;
        return $this;
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
}
