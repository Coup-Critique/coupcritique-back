<?php

namespace App\Entity\Abstracts;

use App\Entity\VideoTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[MappedSuperclass]
abstract class AbstractVideo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:video', 'read:list'])]
    protected $id;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    #[Groups(['read:video', 'insert:video', 'read:list'])]
    #[Assert\Length(max: 128, maxMessage: 'Le titre peut faire au maximum 128 caractères.')]
    protected $title;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read:video', 'insert:video', 'read:list'])]
    #[Assert\Length(max: 255, maxMessage: "L'url peut faire au maximum 255 caractères.")]
    protected $url;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read:video', 'insert:video', 'read:list'])]
    #[Assert\Length(max: 255, maxMessage: "L'url peut faire au maximum 255 caractères.")]
    protected $youtube_id;

    #[ORM\Column(type: 'text', length: 600, nullable: true)]
    #[Groups(['read:video', 'insert:video', 'read:list'])]
    #[Assert\Length(max: 600, maxMessage: 'La description peut faire au maximum 600 caractères.')]
    protected $description;

    #[ORM\Column(type: 'string', length: 128, nullable: true)]
    #[Groups(['read:video', 'insert:video', 'read:list'])]
    #[Assert\Length(max: 128, maxMessage: "L'auteur peut faire au maximum 128 caractères.")]
    protected $author;

    protected $tags;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $youtube_date;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:video', 'read:list'])]
    protected $date_creation;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtube_id;
    }

    public function setYoutubeId(string $youtube_id): self
    {
        $this->youtube_id = $youtube_id;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|VideoTag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(VideoTag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(VideoTag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function getYoutubeDate(): ?\DateTimeInterface
    {
        return $this->youtube_date;
    }

    public function setYoutubeDate(?\DateTimeInterface $youtube_date): self
    {
        $this->youtube_date = $youtube_date;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }
}
