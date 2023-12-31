<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractPokemonSetMove;
use App\Repository\PokemonSetMoveFourRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonSetMoveFourRepository::class)]
class PokemonSetMoveFour extends AbstractPokemonSetMove
{
    #[ORM\ManyToOne(targetEntity: PokemonSet::class, inversedBy: 'moves_set_4')]
    #[ORM\JoinColumn(nullable: false)]
    protected $pokemonSet;
}