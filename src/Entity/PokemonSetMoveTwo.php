<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractPokemonSetMove;
use App\Repository\PokemonSetMoveTwoRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PokemonSetMoveTwoRepository::class)
 */
class PokemonSetMoveTwo extends AbstractPokemonSetMove
{
    /**
     * @ORM\ManyToOne(targetEntity=PokemonSet::class, inversedBy="moves_set_2")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $pokemonSet;
}
