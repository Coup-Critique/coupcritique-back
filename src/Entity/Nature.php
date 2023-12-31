<?php

namespace App\Entity;

use App\Repository\NatureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NatureRepository::class)]
class Nature
{
	#[ORM\Id]
 #[ORM\GeneratedValue]
 #[ORM\Column(type: 'integer')]
 #[Groups(['read:nature', 'read:list', 'read:name', 'read:team', 'insert:team', 'read:usage'])]
 private $id;

	#[ORM\Column(type: 'string', length: 10)]
 #[Groups(['read:nature', 'read:list', 'read:name', 'read:team', 'read:usage'])]
 private $name;

	#[ORM\Column(type: 'string', length: 10, nullable: true)]
 #[Groups(['read:nature', 'read:list', 'read:name', 'read:team', 'read:usage'])]
 private $nom;

	#[ORM\Column(type: 'integer', nullable: true)]
 #[Groups(['read:nature', 'read:list', 'read:team', 'read:usage'])]
 private $atk;

	#[ORM\Column(type: 'integer', nullable: true)]
 #[Groups(['read:nature', 'read:list', 'read:team', 'read:usage'])]
 private $def;

	#[ORM\Column(type: 'integer', nullable: true)]
 #[Groups(['read:nature', 'read:list', 'read:team', 'read:usage'])]
 private $spa;

	#[ORM\Column(type: 'integer', nullable: true)]
 #[Groups(['read:nature', 'read:list', 'read:team', 'read:usage'])]
 private $spd;

	#[ORM\Column(type: 'integer', nullable: true)]
 #[Groups(['read:nature', 'read:list', 'read:team', 'read:usage'])]
 private $spe;

	#[ORM\Column(type: 'text', length: 3000, nullable: true)]
 #[Groups(['read:nature'])]
 #[Assert\Length(max: 3000, maxMessage: 'La description peut faire au maximum 3000 caractères.')]
 private $description;

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

	public function getAtk(): ?int
	{
		return $this->atk;
	}

	public function setAtk(?int $atk): self
	{
		$this->atk = $atk;

		return $this;
	}

	public function getDef(): ?int
	{
		return $this->def;
	}

	public function setDef(?int $def): self
	{
		$this->def = $def;

		return $this;
	}

	public function getSpa(): ?int
	{
		return $this->spa;
	}

	public function setSpa(?int $spa): self
	{
		$this->spa = $spa;

		return $this;
	}

	public function getSpd(): ?int
	{
		return $this->spd;
	}

	public function setSpd(?int $spd): self
	{
		$this->spd = $spd;

		return $this;
	}

	public function getSpe(): ?int
	{
		return $this->spe;
	}

	public function setSpe(?int $spe): self
	{
		$this->spe = $spe;

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
}
