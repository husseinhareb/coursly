<?php
// src/Entity/Course.php
namespace App\Entity;

use App\Repository\CourseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255)]
    private ?string $title = null;
    
    #[ORM\Column(length: 255)]
    private ?string $description = null;
    
    #[ORM\Column(length: 255)]
    private ?string $code = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;
    
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;
    
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $background = null;
    
    private ?File $image = null;
    
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Enrollment::class, cascade: ['persist', 'remove'])]
    private Collection $enrollments;
    
    // The posts collection is the inverse side of the relationship.
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Post::class)]
    private Collection $posts;
    
    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->posts = new ArrayCollection();
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
    
    public function getDescription(): ?string
    {
        return $this->description;
    }
    
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
    
    public function getCode(): ?string
    {
        return $this->code;
    }
    
    public function setCode(string $code): self
    {
        $this->code = $code;
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
    
    public function getBackground(): ?string
    {
        return $this->background;
    }
    
    public function setBackground(?string $background): self
    {
        $this->background = $background;
        return $this;
    }
    
    public function getImage(): ?File
    {
        return $this->image;
    }
    
    public function setImage(?File $image = null): self
    {
        $this->image = $image;
        if ($image) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }
    
    /**
     * @return Collection|Enrollment[]
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }
    
    /**
     * @return Collection|Post[]
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
}
