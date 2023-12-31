<?php

namespace App\Entity;

use App\Entity\Traits\GenProperty;
use App\Repository\WeaknessRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: WeaknessRepository::class)]
#[ORM\Table(name: 'weakness')]
#[ORM\UniqueConstraint(name: 'weakness_gen_unique', columns: ['type_attacker_id', 'type_defender_id', 'gen'])]
class Weakness
{
	use GenProperty;

	#[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 #[Groups(['read:type', 'read:weakness'])]
 private $id;
	#[ORM\ManyToOne(targetEntity: Type::class, inversedBy: 'efficiencies')]
 #[ORM\JoinColumn(nullable: false)]
 #[Groups(['read:weakness', 'read:pokemon'])]
 private $type_attacker;
	#[ORM\ManyToOne(targetEntity: Type::class, inversedBy: 'weaknesses')]
 #[ORM\JoinColumn(nullable: false)]
 #[Groups(['read:weakness', 'read:pokemon'])]
 private $type_defender;
	#[ORM\Column(type: 'float', nullable: true)]
 #[Groups(['read:type', 'read:weakness', 'read:pokemon'])]
 private $ratio;

	public function getId() : ?int
	{
		return $this->id;
	}

	public function getTypeAttacker() : ?Type
	{
		return $this->type_attacker;
	}

	public function setTypeAttacker(?Type $type_attacker) : self
	{
		$this->type_attacker = $type_attacker;

		return $this;
	}

	public function getTypeDefender() : ?Type
	{
		return $this->type_defender;
	}

	public function setTypeDefender(?Type $type_defender) : self
	{
		$this->type_defender = $type_defender;

		return $this;
	}

	public function getRatio() : ?float
	{
		return $this->ratio;
	}

	public function setRatio(?float $ratio) : self
	{
		$this->ratio = $ratio;

		return $this;
	}
}
