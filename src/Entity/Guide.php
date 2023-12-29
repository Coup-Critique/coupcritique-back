<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\Abstracts\AbstractTag;
use App\Repository\GuideRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=GuideRepository::class)
 */
class Guide extends AbstractArticle
{
    /**
     * @ORM\OneToOne(targetEntity=Resource::class, inversedBy="guide", cascade={"persist", "remove"}, fetch="EAGER"))
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"read:article", "read:list", "read:list:article", "insert:article", "update:article", "read:resource"})
     */
    private $resource;

    /**
     * @ORM\ManyToMany(targetEntity=GuideTag::class)
     * @Groups({"read:article", "read:list", "read:list:article", "insert:article", "update:article"})
     */
    private $tags;
    
    /**
     * @ORM\OneToMany(
     *      targetEntity=Comment::class, 
     *      mappedBy="guide", 
     *      cascade={"persist", "remove"}
     * )
     */
    protected $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getResource() : ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): self
    {
         $this->resource = $resource;
         return $this;
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
     * @param GuideTag $tag 
     * @return Guide 
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
     * @param GuideTag $tag 
     * @return Guide 
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
            $comment->setGuide($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getGuide() === $this) {
                $comment->setGuide(null);
            }
        }

        return $this;
    }
}
