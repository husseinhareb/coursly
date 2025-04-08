<?php
// src/Entity/Post.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Course;
use App\Entity\User;

#[ORM\Entity]
#[ORM\Table(name: "posts")]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    private ?string $title = null;
    
    #[ORM\Column(type: "text")]
    private ?string $content = null;
    
    #[ORM\Column(length: 50)]
    private ?string $type = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;
    
    // Many posts belong to one course.
    // Added the 'inversedBy' attribute pointing to the 'posts' property in Course.
    #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: "posts")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;
    
    // Many posts are created by one user (author).
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;
    
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }
    
    public function getContent(): ?string
    {
        return $this->content;
    }
    
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    public function getType(): ?string
    {
        return $this->type;
    }
    
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }
    
    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }
    
    public function getCourse(): ?Course
    {
        return $this->course;
    }
    
    public function setCourse(?Course $course): self
    {
        $this->course = $course;
        return $this;
    }
    
    public function getAuthor(): ?User
    {
        return $this->author;
    }
    
    public function setAuthor(?User $author): self
    {
        $this->author = $author;
        return $this;
    }
}
