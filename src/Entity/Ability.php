<?php

namespace App\Entity;

use App\Entity\Traits\GenProperty;
use App\Repository\AbilityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AbilityRepository::class)
 * @ORM\Table(
 *    name="ability", 
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(
 *            name="ability_name_gen_unique",
 *            columns={"name", "gen"}
 *        )
 *    }
 * )
 */
class Ability
{
	use GenProperty;

	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 * @Groups({"read:ability", "read:list", "read:pokemon", "read:team", "insert:team", "read:usage"})
	 */
	private $id;

	/**
	 * @ORM\Column(type="string", length=50)
	 * @Groups({"read:ability", "read:list", "read:pokemon", "read:team", "read:usage"})
	 */
	private $name;

	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 * @Groups({"read:ability", "read:list", "read:pokemon", "read:team", "read:usage"})
	 */
	private $nom;

	/**
	 * @ORM\Column(type="text", length=3000, nullable=true)
	 * @Groups({"read:ability", "read:own:list", "read:pokemon", "read:team", "read:usage"})
     * @Assert\Length(
     *    max = 3000,
     *    maxMessage="La description peut faire au maximum 3000 caractères."
     * )
	 */
	private $description;
	
	/**
	 * @ORM\Column(type="text", length=3000, nullable=true)
	 */
	private $save_descr;

	/**
	 * @ORM\Column(type="string", length=50)
	 * @Groups({"read:ability"})
	 */
	private $usageName;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $update_date;
    
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $user;

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
	
	public function getUsageName(): ?string
	{
		return $this->usageName;
	}

	public function setUsageName(string $usageName): self
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

	public function getSaveDescr(): ?string
	{
		return $this->save_descr;
	}

	public function setSaveDescr(?string $save_descr): self
	{
		$this->save_descr = $save_descr;

		return $this;
	}
	
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    public function setUpdateDate(?\DateTimeInterface $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }
}
