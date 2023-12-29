<?php

namespace App\Entity;

use App\Entity\Traits\GenProperty;
use App\Repository\PokemonMoveRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PokemonMoveRepository::class)
 * @ORM\Table(
 *    name="pokemon_move", 
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(
 *            name="pokemon_move_name_gen_unique",
 *            columns={"pokemon_id", "move_id", "gen"}
 *        )
 *    }
 * )
 */
class PokemonMove
{
    use GenProperty;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pokemon::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read:list", "read:move"})
     */
    private $pokemon;

    /**
     * @ORM\ManyToOne(targetEntity=Move::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read:list", "read:pokemon"})
     */
    private $move;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read:pokemon"})
     */
    private $way;

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getPokemon() : ?Pokemon
    {
        return $this->pokemon;
    }

    public function setPokemon(?Pokemon $pokemon) : self
    {
        $this->pokemon = $pokemon;

        return $this;
    }

    public function getMove() : ?Move
    {
        return $this->move;
    }

    public function setMove(?Move $move) : self
    {
        $this->move = $move;

        return $this;
    }

    public function getWay() : ?string
    {
        return $this->way;
    }

    public function setWay(?string $way) : self
    {
        $this->way = $way;

        return $this;
    }
}
