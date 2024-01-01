<?php

namespace App\Entity;

use App\Entity\Traits\GenProperty;
use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
#[ORM\Table(name: 'type')]
#[ORM\UniqueConstraint(name: 'type_name_gen_unique', columns: ['name', 'gen'])]
class Type
{
	use GenProperty;

	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column(type: 'integer')]
	#[Groups(['read:list', 'read:type', 'read:name', 'read:list:usage', 'read:usage', 'read:pokemon', 'read:team', 'read:weakness', 'insert:team'])]
	private $id;

	#[ORM\Column(type: 'string', length: 10)]
	#[Groups(['read:list', 'read:type', 'read:name', 'read:list:usage', 'read:usage', 'read:pokemon', 'read:team', 'read:weakness'])]
	private $name;

	#[ORM\Column(type: 'string', length: 10, nullable: true)]
	#[Groups(['read:list', 'read:type', 'read:name', 'read:list:usage', 'read:usage', 'read:pokemon', 'read:team', 'read:weakness'])]
	private $nom;

	#[ORM\Column(type: 'text', length: 3000, nullable: true)]
	#[Groups(['read:type'])]
	#[Assert\Length(max: 3000, maxMessage: 'La description peut faire au maximum 3000 caractères.')]
	private $description;

	#[ORM\OneToMany(targetEntity: Weakness::class, mappedBy: 'type_attacker', cascade: ['persist', 'remove'])]
	private $efficiencies;

	#[ORM\OneToMany(targetEntity: Weakness::class, mappedBy: 'type_defender', cascade: ['persist', 'remove'])]
	private $weaknesses;

	public function __construct()
	{
		$this->efficiencies = new ArrayCollection();
		$this->weaknesses  = new ArrayCollection();
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

	public function getNom(): ?string
	{
		return $this->nom;
	}

	public function setNom(?string $nom): self
	{
		$this->nom = $nom;

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

	/**
	 * @return Collection|Weakness[]
	 */
	public function getEfficiencies(): Collection
	{
		return $this->efficiencies;
	}

	public function addEfficiency(Weakness $efficiency): self
	{
		if (!$this->efficiencies->contains($efficiency)) {
			$this->efficiencies[] = $efficiency;
			$efficiency->setTypeAttacker($this);
		}

		return $this;
	}

	public function removeEfficiency(Weakness $efficiency): self
	{
		if ($this->efficiencies->contains($efficiency)) {
			$this->efficiencies->removeElement($efficiency);
			// set the owning side to null (unless already changed)
			if ($efficiency->getTypeAttacker() === $this) {
				$efficiency->setTypeAttacker(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|Weakness[]
	 */
	public function getWeaknesses(): Collection
	{
		return $this->weaknesses;
	}

	public function addWeakness(Weakness $weakness): self
	{
		if (!$this->weaknesses->contains($weakness)) {
			$this->weaknesses[] = $weakness;
			$weakness->setTypeDefender($this);
		}

		return $this;
	}

	public function removeWeakness(Weakness $weakness): self
	{
		if ($this->weaknesses->contains($weakness)) {
			$this->weaknesses->removeElement($weakness);
			// set the owning side to null (unless already changed)
			if ($weakness->getTypeDefender() === $this) {
				$weakness->setTypeDefender(null);
			}
		}

		return $this;
	}

	public function removeWeaknesses(): self
	{
		$this->weaknesses = new ArrayCollection();
		return $this;
	}
}
