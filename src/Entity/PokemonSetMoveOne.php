<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractPokemonSetMove;
use App\Repository\PokemonSetMoveOneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonSetMoveOneRepository::class)]
class PokemonSetMoveOne extends AbstractPokemonSetMove
{
    #[ORM\ManyToOne(targetEntity: PokemonSet::class, inversedBy: 'moves_set_1')]
    #[ORM\JoinColumn(nullable: false)]
    protected $pokemonSet;
}
