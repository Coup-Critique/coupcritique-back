<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractPokemonSetMove;
use App\Repository\PokemonSetMoveThreeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonSetMoveThreeRepository::class)]
class PokemonSetMoveThree extends AbstractPokemonSetMove
{
    #[ORM\ManyToOne(targetEntity: PokemonSet::class, inversedBy: 'moves_set_3')]
    #[ORM\JoinColumn(nullable: false)]
    protected $pokemonSet;
}
