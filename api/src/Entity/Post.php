<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="title required")
     * @Assert\NotNull(message="title required")
     * @Assert\Length(
     *      max = 100,
     *      maxMessage = "title cannot be longer than {{ limit }} characters"
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="content required")
     * @Assert\NotNull(message="content required")
     */
    private $content;

    /**
     * @ORM\Column(type="date")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $validated;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $validatedAt;

    /**
     * @ORM\OneToMany(targetEntity=PostImage::class, mappedBy="post", orphanRemoval=true)
     */
    private $postImages;

    /**
     * @ORM\ManyToOne(targetEntity=Country::class, inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $relatedCountry;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="relatedPost", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->postImages = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime('now');

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeInterface
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(?\DateTimeInterface $validatedAt): self
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    /**
     * @return Collection|PostImage[]
     */
    public function getPostImages(): Collection
    {
        return $this->postImages;
    }

    public function addPostImage(PostImage $postImage): self
    {
        if (!$this->postImages->contains($postImage)) {
            $this->postImages[] = $postImage;
            $postImage->setPost($this);
        }

        return $this;
    }

    public function removePostImage(PostImage $postImage): self
    {
        if ($this->postImages->removeElement($postImage)) {
            // set the owning side to null (unless already changed)
            if ($postImage->getPost() === $this) {
                $postImage->setPost(null);
            }
        }

        return $this;
    }

    public function getRelatedCountry(): ?Country
    {
        return $this->relatedCountry;
    }

    public function setRelatedCountry(?Country $relatedCountry): self
    {
        $this->relatedCountry = $relatedCountry;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setRelatedPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getRelatedPost() === $this) {
                $comment->setRelatedPost(null);
            }
        }

        return $this;
    }
}
