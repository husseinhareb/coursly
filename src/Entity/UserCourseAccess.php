<?php
// src/Entity/UserCourseAccess.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserCourseAccessRepository;

#[ORM\Entity(repositoryClass: UserCourseAccessRepository::class)]
class UserCourseAccess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;
    
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $user;
    
    #[ORM\ManyToOne(targetEntity: Course::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $course;
    
    #[ORM\Column(type: 'datetime')]
    private $accessedAt;
    
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    
    public function getCourse(): ?Course
    {
        return $this->course;
    }
    
    public function setCourse(Course $course): self
    {
        $this->course = $course;
        return $this;
    }
    
    public function getAccessedAt(): ?\DateTimeInterface
    {
        return $this->accessedAt;
    }
    
    public function setAccessedAt(\DateTimeInterface $accessedAt): self
    {
        $this->accessedAt = $accessedAt;
        return $this;
    }
}
