<?php

namespace App\Entity;

use App\Repository\TeamMateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TeamMateRepository::class)]
class TeamMate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: TierUsage::class, inversedBy: 'teamMates')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $tierUsage;

    #[ORM\ManyToOne(targetEntity: Pokemon::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:usage', 'read:pokemon'])]
    private $pokemon;

    #[ORM\Column(type: 'float')]
    #[Groups(['read:usage', 'read:pokemon'])]
    private $percent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTierUsage(): ?TierUsage
    {
        return $this->tierUsage;
    }

    public function setTierUsage(?TierUsage $tierUsage): self
    {
        $this->tierUsage = $tierUsage;

        return $this;
    }

    public function getPokemon(): ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(?Pokemon $pokemon): self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function setPercent(float $percent): self
    {
        $this->percent = $percent;

        return $this;
    }
}
