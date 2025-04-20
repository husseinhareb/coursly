<?php
// src/Entity/MessageCategory.php

namespace App\Entity;

use App\Entity\Post;
use App\Repository\MessageCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageCategoryRepository::class)]
#[ORM\Table(name: 'message_category')]
class MessageCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $name = '';

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(
        mappedBy: 'category',
        targetEntity: Post::class,
        cascade: ['persist','remove'],
        orphanRemoval: true
    )]
    private Collection $posts;

    /**
     * Allow an optional name to be passed when constructing,
     * but also support Doctrine’s proxy/hydrator requiring a no-arg constructor.
     */
    public function __construct(?string $name = null)
    {
        $this->posts = new ArrayCollection();
        if (null !== $name) {
            $this->setName($name);
        }
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * The category name is required to be non-empty.
     * We trim and ucfirst to standardize.
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $normalized = trim($name);
        if ($normalized === '') {
            throw new \InvalidArgumentException('Category name cannot be empty.');
        }
        $this->name = ucfirst($normalized);
        return $this;
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
            $this->posts->add($post);
            $post->setCategory($this);
        }
        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // Unlink owning side if needed
            if ($post->getCategory() === $this) {
                $post->setCategory(null);
            }
        }
        return $this;
    }
}
