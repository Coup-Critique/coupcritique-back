<?php

namespace App\Entity;

use App\Entity\Traits\GenProperty;
use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PokemonRepository::class)]
#[ORM\Table(name: 'pokemon')]
#[ORM\UniqueConstraint(name: 'pokemon_name_gen_unique', columns: ['name', 'gen'])]
class Pokemon
{
    use GenProperty;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list', 'read:pokemon', 'read:list:usage', 'read:usage', 'read:usage:short', 'read:name', 'read:team', 'read:list:team', 'insert:team'])]
    private $id;

    /**
     * nullable due to import
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:list:usage', 'read:usage'])]
    private $pokedex;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:list:usage', 'read:usage', 'read:usage:short', 'read:name', 'read:team', 'read:list:team'])]
    private $name;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:list:usage', 'read:usage', 'read:usage:short', 'read:name', 'read:team', 'read:list:team'])]
    private $nom;

    #[ORM\Column(type: 'text', length: 3000, nullable: true)]
    #[Groups(['read:pokemon', 'insert:pokemon'])]
    #[Assert\Length(max: 3000, maxMessage: 'La description peut faire au maximum 3000 caractères.')]
    private $description;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:team'])]
    private $hp;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:team'])]
    private $atk;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:team'])]
    private $def;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:team'])]
    private $spa;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:team'])]
    private $spd;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:list', 'read:pokemon', 'insert:pokemon', 'read:team'])]
    private $spe;

    /**
     * @var int|null
     */
    #[Groups(['read:list'])]
    private $bst;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['read:pokemon', 'insert:pokemon'])]
    private $weight;

    #[ORM\ManyToOne(targetEntity: Type::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:list', 'read:list:usage', 'read:pokemon', 'read:team'])]
    private $type_1;

    #[ORM\ManyToOne(targetEntity: Type::class)]
    #[Groups(['read:list', 'read:list:usage', 'read:pokemon', 'read:team'])]
    private $type_2;

    #[ORM\ManyToOne(targetEntity: Ability::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['read:list', 'read:pokemon'])]
    private $ability_1;

    #[ORM\ManyToOne(targetEntity: Ability::class)]
    #[Groups(['read:list', 'read:pokemon'])]
    private $ability_2;

    #[ORM\ManyToOne(targetEntity: Ability::class)]
    #[Groups(['read:list', 'read:pokemon'])]
    private $ability_hidden;

    #[ORM\OneToMany(targetEntity: Pokemon::class, mappedBy: 'base_form')]
    #[Groups('read:pokemon')]
    private $forms;

    #[ORM\ManyToOne(targetEntity: Pokemon::class, inversedBy: 'forms')]
    #[Groups('read:pokemon')]
    private $base_form;

    #[ORM\ManyToOne(targetEntity: Tier::class)]
    #[Groups(['read:list', 'read:pokemon', 'read:list:usage'])]
    private $tier;

    #[ORM\ManyToOne(targetEntity: Tier::class)]
    private $doublesTier;

    #[ORM\ManyToOne(targetEntity: Pokemon::class, inversedBy: 'evolutions')]
    #[Groups('read:pokemon')]
    private $preEvo;

    #[ORM\OneToMany(targetEntity: Pokemon::class, mappedBy: 'preEvo')]
    #[Groups('read:pokemon')]
    private $evolutions;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:list', 'read:pokemon', 'read:list:usage'])]
    private ?bool $technically = false;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['read:pokemon', 'insert:pokemon'])]
    private $usageName;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['read:pokemon'])]
    private $contentJson = [];

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $update_date;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private $user;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => 0])]
    private $deleted = false;

    public function __construct()
    {
        $this->forms      = new ArrayCollection();
        $this->evolutions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPokedex(): ?int
    {
        return $this->pokedex;
    }

    public function setPokedex(?int $pokedex): self
    {
        $this->pokedex = $pokedex;

        return $this;
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

    public function getHp(): ?int
    {
        return $this->hp;
    }

    public function setHp(int $hp): self
    {
        $this->hp = $hp;

        return $this;
    }

    public function getAtk(): ?int
    {
        return $this->atk;
    }

    public function setAtk(int $atk): self
    {
        $this->atk = $atk;

        return $this;
    }

    public function getDef(): ?int
    {
        return $this->def;
    }

    public function setDef(int $def): self
    {
        $this->def = $def;

        return $this;
    }

    public function getSpa(): ?int
    {
        return $this->spa;
    }

    public function setSpa(int $spa): self
    {
        $this->spa = $spa;

        return $this;
    }

    public function getSpd(): ?int
    {
        return $this->spd;
    }

    public function setSpd(int $spd): self
    {
        $this->spd = $spd;

        return $this;
    }

    public function getSpe(): ?int
    {
        return $this->spe;
    }

    public function setSpe(int $spe): self
    {
        $this->spe = $spe;

        return $this;
    }

    public function getBst(): ?int
    {
        if (is_null($this->bst)) {
            $this->bst = $this->getHp() + $this->getAtk() + $this->getDef() + $this->getSpa() + $this->getSpd() + $this->getSpe();
        }
        return $this->bst;
    }

    public function setBst(int $bst): self
    {
        $this->bst = $bst;

        return $this;
    }

    public function getWeight(): ?float
    {
        return $this->weight;
    }

    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getType1(): ?Type
    {
        return $this->type_1;
    }

    public function setType1(?Type $type_1): self
    {
        $this->type_1 = $type_1;

        return $this;
    }

    public function getType2(): ?Type
    {
        return $this->type_2;
    }

    public function setType2(?Type $type_2): self
    {
        $this->type_2 = $type_2;

        return $this;
    }

    public function getAbility1(): ?Ability
    {
        return $this->ability_1;
    }

    public function setAbility1(?Ability $ability_1): self
    {
        $this->ability_1 = $ability_1;

        return $this;
    }

    public function getAbility2(): ?Ability
    {
        return $this->ability_2;
    }

    public function setAbility2(?Ability $ability_2): self
    {
        $this->ability_2 = $ability_2;

        return $this;
    }

    public function getAbilityHidden(): ?Ability
    {
        return $this->ability_hidden;
    }

    public function setAbilityHidden(?Ability $ability_hidden): self
    {
        $this->ability_hidden = $ability_hidden;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getForms(): Collection
    {
        return $this->forms;
    }

    public function addForm(self $form): self
    {
        if (!$this->forms->contains($form)) {
            $this->forms[] = $form;
            $form->setBaseForm($this);
        }

        return $this;
    }

    public function removeForm(self $form): self
    {
        if ($this->forms->contains($form)) {
            $this->forms->removeElement($form);
            $form->setBaseForm(null);
        }

        return $this;
    }

    public function getBaseForm(): ?Pokemon
    {
        return $this->base_form;
    }

    public function setBaseForm(?Pokemon $base_form): self
    {
        $this->base_form = $base_form;
        // DO NOT make addForm on baseForm : src\Service\PokemonManager.php l125

        return $this;
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

    public function getDoublesTier(): ?Tier
    {
        return $this->doublesTier;
    }

    public function setDoublesTier(?Tier $doublesTier): self
    {
        $this->doublesTier = $doublesTier;

        return $this;
    }

    public function getPreEvo(): ?self
    {
        return $this->preEvo;
    }

    public function setPreEvo(?self $preEvo): self
    {
        $this->preEvo = $preEvo;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getEvolutions(): Collection
    {
        return $this->evolutions;
    }

    public function addEvolution(self $evolution): self
    {
        if (!$this->evolutions->contains($evolution)) {
            $this->evolutions[] = $evolution;
            $evolution->setPreEvo($this);
        }

        return $this;
    }

    public function removeEvolution(self $evolution): self
    {
        if ($this->evolutions->removeElement($evolution)) {
            // set the owning side to null (unless already changed)
            if ($evolution->getPreEvo() === $this) {
                $evolution->setPreEvo(null);
            }
        }

        return $this;
    }

    public function getTechnically(): ?bool
    {
        return $this->technically;
    }

    public function setTechnically(?bool $technically): self
    {
        $this->technically = $technically;

        return $this;
    }

    public function getContentJson(): ?array
    {
        return $this->contentJson;
    }

    public function setContentJson(?array $contentJson): self
    {
        $this->contentJson = $contentJson;

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

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}
