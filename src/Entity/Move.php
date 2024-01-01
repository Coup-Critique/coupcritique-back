<?php

namespace App\Entity;

use App\Entity\Traits\GenProperty;
use App\Repository\MoveRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MoveRepository::class)]
#[ORM\Table(name: 'move')]
#[ORM\UniqueConstraint(name: 'move_name_gen_unique', columns: ['name', 'gen'])]
class Move
{
    use GenProperty;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:move', 'read:list', 'read:name', 'read:usage', 'read:team', 'insert:team'])]
    private $id;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['read:move', 'read:list', 'read:name', 'read:usage', 'read:team'])]
    private $name;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read:move', 'read:list', 'read:usage', 'read:team'])]
    private $nom;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['read:move'])]
    private $usageName;

    #[ORM\Column(type: 'text', length: 3000, nullable: true)]
    #[Groups(['read:move', 'read:usage', 'read:team'])]
    #[Assert\Length(max: 3000, maxMessage: 'La description peut faire au maximum 3000 caractères.')]
    private $description;

    #[ORM\Column(type: 'text', length: 3000, nullable: true)]
    private $save_descr;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:move', 'read:list', 'read:team'])]
    private $pp;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['read:move', 'read:list', 'read:team'])]
    private $power;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['read:move', 'read:list', 'read:team'])]
    private $accuracy;

    #[ORM\Column(type: 'string', length: 8, nullable: true)]
    #[Groups(['read:move', 'read:list', 'read:team'])]
    private $category;

    #[ORM\ManyToOne(targetEntity: Type::class)]
    #[Groups(['read:move', 'read:list', 'read:usage', 'read:team'])]
    private $type;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $update_date;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $user;

    public function __construct()
    {
        $this->pokemons = new ArrayCollection();
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

    public function getPp(): ?int
    {
        return $this->pp;
    }

    public function setPp(int $pp): self
    {
        $this->pp = $pp;

        return $this;
    }

    public function getPower(): ?int
    {
        return $this->power;
    }

    public function setPower(?int $power): self
    {
        $this->power = $power;

        return $this;
    }

    public function getAccuracy(): ?int
    {
        return $this->accuracy;
    }

    public function setAccuracy(?int $accuracy): self
    {
        $this->accuracy = $accuracy;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

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
