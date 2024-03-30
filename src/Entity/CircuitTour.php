<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Abstracts\AbstractTag;
use App\Entity\Interfaces\CalendableInterface;
use App\Repository\CircuitTourRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CircuitTourRepository::class)]
class CircuitTour extends AbstractArticle implements CalendableInterface
{
    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:article', 'read:list', 'update:article', 'insert:article'])]
    protected ?DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:article', 'read:list', 'update:article', 'insert:article'])]
    protected ?DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read:article', 'read:list', 'update:article', 'insert:article'])]
    protected ?string $color = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'circuitTour', cascade: ['persist', 'remove'])]
    protected $comments;

    #[ORM\ManyToMany(targetEntity: TournamentTag::class)]
    #[Groups(['read:article', 'read:list', 'read:list:article', 'update:article', 'insert:article'])]
    protected $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
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
     * 
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
            $comment->setCircuitTour($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCircuitTour() === $this) {
                $comment->setCircuitTour(null);
            }
        }

        return $this;
    }
}
