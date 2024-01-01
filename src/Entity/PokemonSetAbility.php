<?php

namespace App\Entity;

use App\Repository\PokemonSetAbilityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PokemonSetAbilityRepository::class)]
class PokemonSetAbility
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:pokemon-set'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: PokemonSet::class, inversedBy: 'abilities_set')]
    #[ORM\JoinColumn(nullable: false)]
    private $pokemonSet;

    #[ORM\ManyToOne(targetEntity: Ability::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:pokemon-set'])]
    private $ability;

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

    public function getAbility(): ?Ability
    {
        return $this->ability;
    }

    public function setAbility(?Ability $ability): self
    {
        $this->ability = $ability;

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
