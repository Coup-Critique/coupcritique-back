<?php

namespace App\Entity;

use App\Repository\PokemonSetNatureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PokemonSetNatureRepository::class)]
class PokemonSetNature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:pokemon-set'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: PokemonSet::class, inversedBy: 'natures_set')]
    #[ORM\JoinColumn(nullable: false)]
    private $pokemonSet;

    #[ORM\ManyToOne(targetEntity: Nature::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:pokemon-set'])]
    private $nature;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:pokemon-set'])]
    private $rank;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPokemonSet(): ?PokemonSet
    {
        return $this->pokemonSet;
    }

    public function setPokemonSet(?PokemonSet $pokemonSet): self
    {
        $this->pokemonSet = $pokemonSet;

        return $this;
    }

    public function getNature(): ?Nature
    {
        return $this->nature;
    }

    public function setNature(?Nature $nature): self
    {
        $this->nature = $nature;

        return $this;
    }
    
    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }
}
