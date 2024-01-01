<?php

namespace App\Entity\Abstracts;

use App\Entity\Move;
use App\Entity\PokemonSet;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Symfony\Component\Serializer\Attribute\Groups;

#[MappedSuperclass]
abstract class AbstractPokemonSetMove
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:pokemon-set'])]
    protected $id;

    protected $pokemonSet;

    #[ORM\ManyToOne(targetEntity: Move::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:pokemon-set'])]
    protected $move;
    
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:pokemon-set'])]
    protected $rank;

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

    public function getMove(): ?Move
    {
        return $this->move;
    }

    public function setMove(?Move $move): self
    {
        $this->move = $move;

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
