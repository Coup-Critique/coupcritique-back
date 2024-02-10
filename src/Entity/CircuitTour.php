<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Interfaces\CalendableInterface;
use App\Repository\CircuitTourRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CircuitTourRepository::class)]
class CircuitTour extends AbstractArticle implements CalendableInterface
{
    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:article', 'read:list', 'update:article', 'insert:article'])]
    private ?DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:article', 'read:list', 'update:article', 'insert:article'])]
    private ?DateTimeInterface $endDate = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['read:article', 'read:list', 'update:article', 'insert:article'])]
    private ?string $color = null;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'circuitTour', cascade: ['persist', 'remove'])]
    protected $comments;

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
