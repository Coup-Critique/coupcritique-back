<?php

namespace App\Entity;

use App\Repository\PokemonSetItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PokemonSetItemRepository::class)
 */
class PokemonSetItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:pokemon-set"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PokemonSet::class, inversedBy="items_set")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pokemonSet;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read:pokemon-set"})
     */
    private $item;

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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

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
