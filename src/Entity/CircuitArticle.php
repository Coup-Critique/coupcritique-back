<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Abstracts\AbstractTag;
use App\Entity\Interfaces\HasTourInterface;
use App\Repository\CircuitArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CircuitArticleRepository::class)]
class CircuitArticle extends AbstractArticle implements HasTourInterface
{

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'circuitArticle', cascade: ['persist', 'remove'])]
    protected $comments;

    #[ORM\ManyToMany(targetEntity: TournamentTag::class)]
    #[Groups(['read:article', 'read:list', 'read:list:article', 'update:article', 'insert:article'])]
    protected $tags;

    #[ORM\ManyToOne(targetEntity: CircuitTour::class, inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['read:article', 'update:article', 'insert:article'])]
    private $tour;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return Collection|TournamentTag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * 
     * @param TournamentTag $tag 
     * @return Tournament 
     */
    public function addTag(AbstractTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    /**
     * @param TournamentTag $tag 
     * @return Tournament 
     */
    public function removeTag(AbstractTag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

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
            $comment->setCircuitArticle($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCircuitArticle() === $this) {
                $comment->setCircuitArticle(null);
            }
        }

        return $this;
    }

    public function getTour(): ?CircuitTour
    {
        return $this->tour;
    }

    public function setTour(?CircuitTour $tour): self
    {
        $this->tour = $tour;

        return $this;
    }
}
