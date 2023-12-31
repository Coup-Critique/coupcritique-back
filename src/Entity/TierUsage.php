<?php

namespace App\Entity;

use App\Repository\TierUsageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TierUsageRepository::class)]
class TierUsage
{
	#[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 #[Groups(['read:usage', 'read:usage:short', 'read:list', 'read:list:usage', 'read:pokemon'])]
 private $id;

	#[ORM\ManyToOne(targetEntity: Tier::class)]
 #[ORM\JoinColumn(nullable: false)]
 #[Groups(['read:usage', 'read:pokemon', 'read:usage:short'])]
 private $tier;

	#[ORM\ManyToOne(targetEntity: Pokemon::class)]
 #[ORM\JoinColumn(nullable: false)]
 #[Groups(['read:usage', 'read:list', 'read:list:usage', 'read:usage:short'])]
 private $pokemon;

	#[ORM\Column(type: 'integer', nullable: true)]
 #[Groups(['read:usage', 'read:list', 'read:list:usage', 'read:pokemon', 'read:usage:short'])]
 private $rank;

	#[ORM\Column(type: 'float', nullable: true)]
 #[Groups(['read:usage', 'read:list', 'read:list:usage', 'read:pokemon', 'read:usage:short'])]
 private $percent;

	#[ORM\OneToMany(targetEntity: UsageAbility::class, mappedBy: 'tierUsage', orphanRemoval: true, cascade: ['persist', 'remove'])]
 #[Groups(['read:usage', 'read:pokemon'])]
 private $usageAbilities;

	#[ORM\OneToMany(targetEntity: UsageItem::class, mappedBy: 'tierUsage', orphanRemoval: true, cascade: ['persist', 'remove'])]
 #[Groups(['read:usage'])]
 private $usageItems;

	#[ORM\OneToMany(targetEntity: UsageMove::class, mappedBy: 'tierUsage', orphanRemoval: true, cascade: ['persist', 'remove'])]
 #[Groups(['read:usage'])]
 private $usageMoves;

	#[ORM\OneToMany(targetEntity: UsageSpread::class, mappedBy: 'tierUsage', orphanRemoval: true, cascade: ['persist', 'remove'])]
 #[Groups(['read:usage'])]
 private $usageSpreads;

	#[ORM\OneToMany(targetEntity: TeamMate::class, mappedBy: 'tierUsage', orphanRemoval: true, cascade: ['persist', 'remove'])]
 #[Groups(['read:usage'])]
 private $teamMates;

	// * @Groups({"read:usage"})
	/**
	 * @ORM\
	 *   OneToMany(targetEntity=PokemonCheck::class,
	 *   mappedBy="tierUsage", 
	 *   orphanRemoval=true, 
	 *   cascade={"persist", "remove"}
	 * )
	 */
	private $pokemonChecks;

	public function __construct()
	{
		$this->usageAbilities = new ArrayCollection();
		$this->usageItems = new ArrayCollection();
		$this->usageMoves = new ArrayCollection();
		$this->teamMates = new ArrayCollection();
		$this->pokemonChecks = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getTier(): ?Tier
	{
		return $this->tier;
	}

	public function setTier(?Tier $tier): self
	{
		$this->tier = $tier;

		return $this;
	}

	public function getPokemon(): ?Pokemon
	{
		return $this->pokemon;
	}

	public function setPokemon(?Pokemon $pokemon): self
	{
		$this->pokemon = $pokemon;

		return $this;
	}

	public function getRank(): ?int
	{
		return $this->rank;
	}

	public function setRank(?int $rank): self
	{
		$this->rank = $rank;

		return $this;
	}

	public function getPercent(): ?float
	{
		return $this->percent;
	}

	public function setPercent(?float $percent): self
	{
		$this->percent = $percent;

		return $this;
	}

	/**
	 * @return Collection|UsageAbility[]
	 */
	public function getUsageAbilities(): Collection
	{
		return $this->usageAbilities;
	}

	public function addUsageAbility(UsageAbility $usageAbility): self
	{
		if (!$this->usageAbilities->contains($usageAbility)) {
			$this->usageAbilities[] = $usageAbility;
			$usageAbility->setTierUsage($this);
		}

		return $this;
	}

	public function removeUsageAbility(UsageAbility $usageAbility): self
	{
		if ($this->usageAbilities->removeElement($usageAbility)) {
			// set the owning side to null (unless already changed)
			if ($usageAbility->getTierUsage() === $this) {
				$usageAbility->setTierUsage(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|UsageItem[]
	 */
	public function getUsageItems(): Collection
	{
		return $this->usageItems;
	}

	public function addUsageItem(UsageItem $usageItem): self
	{
		if (!$this->usageItems->contains($usageItem)) {
			$this->usageItems[] = $usageItem;
			$usageItem->setTierUsage($this);
		}

		return $this;
	}

	public function removeUsageItem(UsageItem $usageItem): self
	{
		if ($this->usageItems->removeElement($usageItem)) {
			// set the owning side to null (unless already changed)
			if ($usageItem->getTierUsage() === $this) {
				$usageItem->setTierUsage(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|UsageSpread[]
	 */
	public function getUsageSpreads(): Collection
	{
		return $this->usageSpreads;
	}

	public function addUsageSpread(UsageSpread $usageSpread): self
	{
		if (!$this->usageSpreads->contains($usageSpread)) {
			$this->usageSpreads[] = $usageSpread;
			$usageSpread->setTierUsage($this);
		}

		return $this;
	}

	public function removeUsageSpread(UsageSpread $usageSpread): self
	{
		if ($this->usageSpreads->removeElement($usageSpread)) {
			// set the owning side to null (unless already changed)
			if ($usageSpread->getTierUsage() === $this) {
				$usageSpread->setTierUsage(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|UsageMove[]
	 */
	public function getUsageMoves(): Collection
	{
		return $this->usageMoves;
	}

	public function addUsageMove(UsageMove $usageMove): self
	{
		if (!$this->usageMoves->contains($usageMove)) {
			$this->usageMoves[] = $usageMove;
			$usageMove->setTierUsage($this);
		}

		return $this;
	}

	public function removeUsageMove(UsageMove $usageMove): self
	{
		if ($this->usageMoves->removeElement($usageMove)) {
			// set the owning side to null (unless already changed)
			if ($usageMove->getTierUsage() === $this) {
				$usageMove->setTierUsage(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|TeamMate[]
	 */
	public function getTeamMates(): Collection
	{
		return $this->teamMates;
	}

	public function addTeamMate(TeamMate $teamMate): self
	{
		if (!$this->teamMates->contains($teamMate)) {
			$this->teamMates[] = $teamMate;
			$teamMate->setTierUsage($this);
		}

		return $this;
	}

	public function removeTeamMate(TeamMate $teamMate): self
	{
		if ($this->teamMates->removeElement($teamMate)) {
			// set the owning side to null (unless already changed)
			if ($teamMate->getTierUsage() === $this) {
				$teamMate->setTierUsage(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|PokemonCheck[]
	 */
	public function getPokemonChecks(): Collection
	{
		return $this->pokemonChecks;
	}

	public function addPokemonCheck(PokemonCheck $pokemonCheck): self
	{
		if (!$this->pokemonChecks->contains($pokemonCheck)) {
			$this->pokemonChecks[] = $pokemonCheck;
			$pokemonCheck->setTierUsage($this);
		}

		return $this;
	}

	public function removePokemonCheck(PokemonCheck $pokemonCheck): self
	{
		if ($this->pokemonChecks->removeElement($pokemonCheck)) {
			// set the owning side to null (unless already changed)
			if ($pokemonCheck->getTierUsage() === $this) {
				$pokemonCheck->setTierUsage(null);
			}
		}

		return $this;
	}
}
