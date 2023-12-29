<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractTag;
use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag extends AbstractTag
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read:list", "read:list:team", "read:team"})
     */
    protected $isModo;

    public function getIsModo(): ?bool
    {
        return $this->isModo;
    }

    public function setIsModo(bool $isModo): self
    {
        $this->isModo = $isModo;

        return $this;
    }
}
