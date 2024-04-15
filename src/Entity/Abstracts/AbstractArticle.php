<?php

namespace App\Entity\Abstracts;

use App\Entity\User;
use App\Validator\Constraints as CustomAssert;
use App\Entity\Interfaces\CommentParentInterface;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractArticle implements CommentParentInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:article', 'read:list'])]
    protected $id;

    #[ORM\Column(type: 'string', length: 150)]
    #[Groups(['read:article', 'read:list'])]
    #[Assert\Length(max: 255, maxMessage: 'Le titre peut faire au maximum 255 caractères.')]
    protected $title;

    #[CustomAssert\HtmlTagConstraint()]
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['read:article'])]
    #[Assert\Length(max: 50000, maxMessage: 'La description peut faire au maximum 20000 caractères.')]
    protected $description;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['read:article'])]
    protected $parsedDescription;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    #[Groups(['read:article', 'read:list'])]
    #[Assert\Length(max: 150, maxMessage: 'La description peut faire au maximum 150 caractères.')]
    protected $shortDescription;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['read:article', 'read:list'])]
    protected ?array $images = [];

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:article', 'read:list'])]
    protected $user;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:article', 'read:list'])]
    protected ?DateTimeInterface $date_creation = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?DateTimeInterface $update_date = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getParsedDescription(): ?string
    {
        return $this->parsedDescription;
    }

    public function setParsedDescription(?string $parsedDescription): self
    {
        $this->parsedDescription = $parsedDescription;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDateCreation(): ?DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getUpdateDate(): ?DateTimeInterface
    {
        return $this->update_date;
    }

    public function setUpdateDate(?DateTimeInterface $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }
}
