<?php

namespace App\Entity\Abstracts;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Annotation\Groups;

/** @MappedSuperclass */
abstract class AbstractTag
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read:list","read:list:team","read:list:article", "read:video","read:article", "read:team", "insert:team", "update:team"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=25)
     * @Groups({"read:list","read:list:team", "read:team","read:list:article","read:video","read:article"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"read:list","read:list:team", "read:team","read:list:article","read:video", "read:article"})
     */
    protected $shortName;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:list", "read:list:team", "read:team", "read:list:article", "read:video", "read:article"})
     */
    protected $sortOrder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}
