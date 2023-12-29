<?php

namespace App\Entity;

use App\Repository\ResourceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * @ORM\Entity(repositoryClass=ResourceRepository::class)
 */
class Resource
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:resource", "read:tier", "read:list"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:resource", "read:tier", "read:list"})
     * @Assert\NotNull(message="L'url est requise.")
     * @Assert\Length(
     *    max = 255,
     *    maxMessage="L'url peut faire au maximum 255 caractères."
     * )
     */
    private $url;

    /**
     * @ORM\Column(type="string", length=150)
     * @Groups({"read:resource", "read:tier", "read:list"})
     * @Assert\NotNull(message="Le titre est requis.")
     * @Assert\Length(
     *    max = 150,
     *    maxMessage="Le titre peut faire au maximum 150 caractères."
     * )
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity=Tier::class, inversedBy="resources")
     * @Groups({"read:resource"})
     */
    private $tier;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read:resource", "read:list"})
     * @Assert\Length(
     *    max = 100,
     *    maxMessage="La catégorie peut faire au maximum 100 caractères."
     * )
     */
    private $category;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"read:resource", "read:list"})
     */
    private $gen;

    /**
     * @ORM\OneToOne(targetEntity=Guide::class, mappedBy="resource")
     * @Groups({"read:article", "read:resource"})
     */
    private $guide;


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
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

    public function getTier(): ?Tier
    {
        return $this->tier;
    }

    public function setTier(?Tier $tier): self
    {
        $this->tier = $tier;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function setGen($gen): self
    {
        $this->gen = $gen;
        return $this;
    }

    public function getGen(): ?int
    {
        return $this->gen;
    }

    public function setGuide(?Guide $guide)
    {
        return $this->guide = $guide;
    }

    public function getGuide() : ?Guide
    {
        return $this->guide;
    }
}
