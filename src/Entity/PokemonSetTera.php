<?php

namespace App\Entity;

use App\Repository\PokemonSetTeraRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PokemonSetTeraRepository::class)
 */
class PokemonSetTera
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:pokemon-set"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PokemonSet::class, inversedBy="teras_set")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pokemonSet;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read:pokemon-set"})
     */
    private $tera;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:pokemon-set"})
     */
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

    public function getTera(): ?Type
    {
        return $this->tera;
    }

    public function setTera(?Type $type): self
    {
        $this->tera = $type;

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
