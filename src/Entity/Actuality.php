<?php

namespace App\Entity;


use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Abstracts\AbstractTag;
use App\Repository\ActualityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ActualityRepository::class)]
class Actuality extends AbstractArticle
{
    #[ORM\ManyToMany(targetEntity: ActualityTag::class)]
    #[Groups(['read:article', 'read:list', 'read:list:article', 'update:article', 'insert:article'])]
    private $tags;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'actuality', cascade: ['persist', 'remove'])]
    protected $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return Collection|ActualityTag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * 
     * @param ActualityTag $tag 
     * @return Actuality 
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
     * @param ActualityTag $tag 
     * @return Actuality 
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
            $comment->setActuality($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getActuality() === $this) {
                $comment->setActuality(null);
            }
        }

        return $this;
    }
}
