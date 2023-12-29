<?php

namespace App\Entity;

use App\Entity\Traits\GenProperty;
use App\Repository\TierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TierRepository::class)
 * @ORM\Table(
 *    name="tier", 
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(
 *            name="tier_name_gen_unique",
 *            columns={"name", "gen"}
 *        )
 *    }
 * )
 */
class Tier
{

	const BASE_LADDER_REF = 1630;

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 * @Groups({
	 *   "read:tier", 
	 *   "read:list", 
	 *   "read:list:usage", 
	 *   "read:usage",
	 *   "read:usage:short",
	 *   "read:name",
	 *   "read:pokemon", 
	 *   "read:team", 
	 *   "insert:team", 
	 *   "read:resource", 
	 *   "read:list:team"
	 * })
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=100)
	 * @Groups({
	 *   "read:tier", 
	 *   "read:list", 
	 *   "read:list:usage", 
	 *   "read:usage", 
	 *   "read:usage:short",
	 *   "read:name", 
	 *   "read:pokemon", 
	 *   "read:team", 
	 *   "read:resource", 
	 *   "read:list:team"
	 * })
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", 
	 *   length=30, 
	 *   nullable=true)
	 * @Groups({
	 *   "read:tier", 
	 *   "read:list", 
	 *   "read:list:usage", 
	 *   "read:usage:short",
	 *   "read:usage", 
	 *   "read:name", 
	 *   "read:pokemon", 
	 *   "read:team", 
	 *   "read:resource", 
	 *   "read:list:team"
	 * })
	 */
	private $shortName;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 * @Groups({"read:tier", "read:pokemon"})
	 */
	private $usageName;

	/**
	 * @ORM\Column(type="text", length=3000, nullable=true)
	 * @Groups({"read:tier"})
	 * @Assert\Length(
	 *    max = 3000,
	 *    maxMessage="La description peut faire au maximum 3000 caractères."
	 * )
	 */
	private $description;

	/**
	 * @ORM\Column(type="boolean")
	 * Cannot create a team for unplayable tier but it's not usable show in tier detail
	 * @Groups({"read:list", "read:pokemon"})
	 */
	private ?bool $playable = false;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private ?int $maxPokemon = 6;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 * @Groups({"read:tier", "read:pokemon"})
	 */
	private ?int $ladderRef = self::BASE_LADDER_REF;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 * @Groups({"read:list", "read:list:usage", "read:team"})
	 */
	private $rank;

	/**
	 * @ORM\Column(type="smallint", nullable=true)
	 * @Groups({"read:list", "read:list:usage", "read:team", "read:resource"})
	 */
	private $sortOrder;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Groups({"read:list", "read:team"})
	 */
	private ?bool $isDouble = false;

	/**
	 * @ORM\OneToMany(targetEntity=Resource::class, mappedBy="tier")
	 * @Groups({"read:tier"})
	 */
	private $resources;

	/**
	 * @ORM\ManyToOne(targetEntity=Tier::class, cascade={"persist"})
	 * @Groups({"read:pokemon"})
	 */
	private $parent;

	/**
	 * @ORM\Column(type="integer",nullable=true)
	 * @Groups({
	 *   "read:tier", 
	 *   "read:list", 
	 *   "read:list:usage", 
	 *   "read:usage", 
	 *   "read:usage:short",
	 *   "read:pokemon", 
	 *   "insert:pokemon", 
	 *   "read:team", 
	 *   "read:list:team"
	 * })
	 */
	private int $gen;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Groups({"read:team"})
	 */
	private ?bool $teraBan = false;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Groups({"read:list"})
	 */
	private ?bool $official = false;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 * @Groups({"read:list"})
	 */
	private ?bool $main = false;

	public function setGen(int $gen): self
	{
		$this->gen = $gen;
		return $this;
	}

	public function getGen(): int
	{
		return $this->gen;
	}

	public function __construct()
	{
		$this->resources = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$this->name = $name;

		return $this;
	}

	public function getShortName(): ?string
	{
		return $this->shortName;
	}

	public function setShortName(?string $shortName): self
	{
		$this->shortName = $shortName;

		return $this;
	}

	public function getUsageName(): ?string
	{
		return $this->usageName;
	}

	public function setUsageName(?string $usageName): self
	{
		$this->usageName = $usageName;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(?string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getPlayable(): ?bool
	{
		return $this->playable;
	}

	public function setPlayable(?bool $playable): self
	{
		$this->playable = $playable;

		return $this;
	}

	public function getMaxPokemon(): ?int
	{
		return $this->maxPokemon;
	}

	public function setMaxPokemon(?int $maxPokemon): self
	{
		$this->maxPokemon = $maxPokemon;

		return $this;
	}

	public function getLadderRef(): ?int
	{
		return $this->ladderRef ?: self::BASE_LADDER_REF;
	}

	public function setLadderRef(?int $ladderRef): self
	{
		$this->ladderRef = $ladderRef;

		return $this;
	}

	public function setRank(?int $rank): self
	{
		$this->rank = $rank;
		return $this;
	}

	public function getRank(): ?int
	{
		return $this->rank;
	}

	public function setSortOrder(?int $sortOrder): self
	{
		$this->sortOrder = $sortOrder;
		return $this;
	}

	public function getSortOrder(): ?int
	{
		return $this->sortOrder;
	}

	public function setIsDouble(?bool $isDouble): self
	{
		$this->isDouble = $isDouble;
		return $this;
	}

	public function getIsDouble(): ?bool
	{
		return $this->isDouble;
	}

	/**
	 * @return Collection|Resource[]
	 */
	public function getResources(): Collection
	{
		return $this->resources;
	}

	public function addResource(Resource $resource): self
	{
		if (!$this->resources->contains($resource)) {
			$this->resources[] = $resource;
			$resource->setTier($this);
		}

		return $this;
	}

	public function removeResource(Resource $resource): self
	{
		if ($this->resources->removeElement($resource)) {
			// set the owning side to null (unless already changed)
			if ($resource->getTier() === $this) {
				$resource->setTier(null);
			}
		}

		return $this;
	}

	public function getParent(): ?self
	{
		return $this->parent;
	}

	public function setParent(?self $parent): self
	{
		$this->parent = $parent;

		return $this;
	}

	public function getTeraBan(): ?bool
	{
		return $this->teraBan;
	}

	public function setTeraBan(?bool $teraBan): self
	{
		$this->teraBan = $teraBan;

		return $this;
	}

	public function getOfficial(): ?bool
	{
		return $this->official;
	}

	public function setOfficial(?bool $official): self
	{
		$this->official = $official;

		return $this;
	}

	public function getMain(): ?bool
	{
		return $this->main;
	}

	public function setMain(?bool $main): self
	{
		$this->main = $main;

		return $this;
	}
}
