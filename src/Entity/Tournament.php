<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Abstracts\AbstractTag;
use App\Repository\TournamentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;


#[ORM\Entity(repositoryClass: TournamentRepository::class)]
class Tournament extends AbstractArticle
{
    #[ORM\ManyToMany(targetEntity: TournamentTag::class)]
    #[Groups(['read:article', 'read:list', 'read:list:article', 'update:article', 'insert:article'])]
    private $tags;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'tournament', cascade: ['persist', 'remove'])]
    protected $comments;

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
            $comment->setTournament($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTournament() === $this) {
                $comment->setTournament(null);
            }
        }

        return $this;
    }
}
