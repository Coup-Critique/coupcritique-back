<?php

namespace App\Entity;

use App\Entity\Interfaces\MessageableInterface;
use App\Repository\PokemonSetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * @ORM\Entity(repositoryClass=PokemonSetRepository::class)
 * @CustomAssert\PokemonSetTierConstraint
 */
class PokemonSet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:pokemon-set", "update:set"})
     */
    private $id;

    /**
     * ManyToOne because it isn't mapped in PokemonInstance
     * @ORM\ManyToOne(targetEntity=PokemonInstance::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read:pokemon-set", "update:set"})
     * @Assert\Valid
     * @Assert\NotNull(message="Le tier est requis.")
     */
    private $instance;

    /**
     * @ORM\ManyToOne(targetEntity=Tier::class)
     * @Groups({"read:pokemon-set", "update:set"})
     * @Assert\NotNull(message="Le tier est requis.")
     * @Assert\Valid
     */
    private $tier;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"read:pokemon-set", "update:set"})
     * @Assert\NotNull(message="La génération est requise.")
     * @CustomAssert\GenConstraint()
     */
    private $gen;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"read:pokemon-set", "update:set"})
     * @Assert\NotNull(message="La description est requise.")
     * @Assert\Length(
     *    max = 10000,
     *    maxMessage="Le contenu peut faire au maximum 10000 caractères."
     * )
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"read:pokemon-set", "update:set"})
     * @Assert\NotNull(message="Le nom est requis.")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     * @Groups({"read:pokemon-set", "update:set"})
     */
    private $export;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetItem::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      max = 5,
     *      maxMessage = "Un set comport au maximum {{ limit }} objets."
     * )
     */
    private $items_set;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetTera::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      max = 5,
     *      maxMessage = "Un set comport au maximum {{ limit }} teracristaux."
     * )
     */
    private $teras_set;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetAbility::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      max = 3,
     *      maxMessage = "Un set comport au maximum {{ limit }} talents."
     * )
     */
    private $abilities_set;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetNature::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      max = 5,
     *      maxMessage = "Un set comport au maximum {{ limit }} natures."
     * )
     */
    private $natures_set;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetMoveOne::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      min = 1,
     *      max = 5,
     *      minMessage = "Un set comporte au moins une capacité.",
     *      maxMessage = "Un set comport au maximum {{ limit }} capacités par slot."
     * )
     */
    private $moves_set_1;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetMoveTwo::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      max = 5,
     *      maxMessage = "Un set comport au maximum {{ limit }} capacités par slot."
     * )
     */
    private $moves_set_2;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetMoveThree::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      max = 5,
     *      maxMessage = "Un set comport au maximum {{ limit }} capacités par slot."
     * )
     */
    private $moves_set_3;

    /**
     * @ORM\OneToMany(targetEntity=PokemonSetMoveFour::class, mappedBy="pokemonSet", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups({"read:pokemon-set"})
     * @Assert\Count(
     *      max = 5,
     *      maxMessage = "Un set comport au maximum {{ limit }} capacités par slot."
     * )
     */
    private $moves_set_4;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"read:pokemon-set"})
     */
    private $contentJson = [];

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @Groups({"read:pokemon-set"})
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read:pokemon-set"})
     */
    protected $date_creation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read:pokemon-set"})
     */
    private $update_date;

    public function __construct()
    {
        $this->items_set     = new ArrayCollection();
        $this->teras_set     = new ArrayCollection();
        $this->abilities_set = new ArrayCollection();
        $this->natures_set   = new ArrayCollection();
        $this->moves_set_1   = new ArrayCollection();
        $this->moves_set_2   = new ArrayCollection();
        $this->moves_set_3   = new ArrayCollection();
        $this->moves_set_4   = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInstance(): ?PokemonInstance
    {
        return $this->instance;
    }

    public function setInstance(?PokemonInstance $instance): self
    {
        $this->instance = $instance;
        $this->instance->setPokemonSet($this);
        if ($instance->getItem()) {
            $this->items_set = new ArrayCollection();
            $item_set = new PokemonSetItem();
            $item_set->setItem($instance->getItem());
            $item_set->setRank(0);
            $this->addItemSet($item_set);
        }
        if ($instance->getTera()) {
            $this->teras_set = new ArrayCollection();
            $tera_set = new PokemonSetTera();
            $tera_set->setTera($instance->getTera());
            $tera_set->setRank(0);
            $this->addTeraSet($tera_set);
        }
        if ($instance->getAbility()) {
            $this->abilities_set = new ArrayCollection();
            $ability_set = new PokemonSetAbility();
            $ability_set->setAbility($instance->getAbility());
            $ability_set->setRank(0);
            $this->addAbilitySet($ability_set);
        }
        if ($instance->getNature()) {
            $this->natures_set = new ArrayCollection();
            $nature_set = new PokemonSetNature();
            $nature_set->setNature($instance->getNature());
            $nature_set->setRank(0);
            $this->addNatureSet($nature_set);
        }
        if ($instance->getMove1()) {
            $this->moves_set_1 = new ArrayCollection();
            $move_set_1 = new PokemonSetMoveOne();
            $move_set_1->setMove($instance->getMove1());
            $move_set_1->setRank(0);
            $this->addMoveSet1($move_set_1);
        }
        if ($instance->getMove2()) {
            $this->moves_set_2 = new ArrayCollection();
            $move_set_2 = new PokemonSetMoveTwo();
            $move_set_2->setMove($instance->getMove2());
            $move_set_2->setRank(0);
            $this->addMoveSet2($move_set_2);
        }
        if ($instance->getMove3()) {
            $this->moves_set_3 = new ArrayCollection();
            $move_set_3 = new PokemonSetMoveThree();
            $move_set_3->setMove($instance->getMove3());
            $move_set_3->setRank(0);
            $this->addMoveSet3($move_set_3);
        }
        if ($instance->getMove4()) {
            $this->moves_set_4 = new ArrayCollection();
            $move_set_4 = new PokemonSetMoveFour();
            $move_set_4->setMove($instance->getMove4());
            $move_set_4->setRank(0);
            $this->addMoveSet4($move_set_4);
        }

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

    public function setGen($gen): self
    {
        $this->gen = $gen;
        return $this;
    }

    public function getGen(): ?int
    {
        return $this->gen;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getExport(): ?string
    {
        return $this->export;
    }

    public function setExport(?string $export): self
    {
        $this->export = $export;

        return $this;
    }

    /**
     * @return Collection|PokemonSetItem[]
     */
    public function getItemsSet(): Collection
    {
        return $this->items_set;
    }

    public function addItemSet(PokemonSetItem $item_set): self
    {
        if (!$this->items_set->contains($item_set)) {
            $this->items_set[] = $item_set;
            $item_set->setPokemonSet($this);
        }

        return $this;
    }

    public function removeItemSet(PokemonSetItem $item): self
    {
        $this->items_set->removeElement($item);

        return $this;
    }

    /**
     * @return Collection|PokemonSetTera[]
     */
    public function getTerasSet(): Collection
    {
        return $this->teras_set;
    }

    public function addTeraSet(PokemonSetTera $tera_set): self
    {
        if (!$this->teras_set->contains($tera_set)) {
            $this->teras_set[] = $tera_set;
            $tera_set->setPokemonSet($this);
        }

        return $this;
    }

    public function removeTeraSet(PokemonSetTera $tera): self
    {
        $this->teras_set->removeElement($tera);

        return $this;
    }

    /**
     * @return Collection|PokemonSetAbility[]
     */
    public function getAbilitiesSet(): Collection
    {
        return $this->abilities_set;
    }

    public function addAbilitySet(PokemonSetAbility $ability_set): self
    {
        if (!$this->abilities_set->contains($ability_set)) {
            $this->abilities_set[] = $ability_set;
            $ability_set->setPokemonSet($this);
        }

        return $this;
    }

    public function removeAbilitySet(PokemonSetAbility $ability_set): self
    {
        $this->abilities_set->removeElement($ability_set);

        return $this;
    }

    /**
     * @return Collection|PokemonSetNature[]
     */
    public function getNaturesSet(): Collection
    {
        return $this->natures_set;
    }

    public function addNatureSet(PokemonSetNature $nature_set): self
    {
        if (!$this->natures_set->contains($nature_set)) {
            $this->natures_set[] = $nature_set;
            $nature_set->setPokemonSet($this);
        }

        return $this;
    }

    public function removeNatureSet(PokemonSetNature $nature_set): self
    {
        $this->natures_set->removeElement($nature_set);

        return $this;
    }

    /**
     * @return Collection|PokemonSetMoveOne[]
     */
    public function getMovesSet1(): Collection
    {
        return $this->moves_set_1;
    }

    public function addMoveSet1(PokemonSetMoveOne $move_set_1): self
    {
        if (!$this->moves_set_1->contains($move_set_1)) {
            $this->moves_set_1[] = $move_set_1;
            $move_set_1->setPokemonSet($this);
        }

        return $this;
    }

    public function removeMoveSet1(PokemonSetMoveOne $move_set_1): self
    {
        $this->moves_set_1->removeElement($move_set_1);

        return $this;
    }

    /**
     * @return Collection|PokemonSetMoveTwo[]
     */
    public function getMovesSet2(): Collection
    {
        return $this->moves_set_2;
    }

    public function addMoveSet2(PokemonSetMoveTwo $move_set_2): self
    {
        if (!$this->moves_set_2->contains($move_set_2)) {
            $this->moves_set_2[] = $move_set_2;
            $move_set_2->setPokemonSet($this);
        }

        return $this;
    }

    public function removeMoveSet2(PokemonSetMoveTwo $move_set_2): self
    {
        $this->moves_set_2->removeElement($move_set_2);

        return $this;
    }

    /**
     * @return Collection|PokemonSetMoveThree[]
     */
    public function getMovesSet3(): Collection
    {
        return $this->moves_set_3;
    }

    public function addMoveSet3(PokemonSetMoveThree $move_set_3): self
    {
        if (!$this->moves_set_3->contains($move_set_3)) {
            $this->moves_set_3[] = $move_set_3;
            $move_set_3->setPokemonSet($this);
        }

        return $this;
    }

    public function removeMoveSet3(PokemonSetMoveThree $move_set_3): self
    {
        $this->moves_set_3->removeElement($move_set_3);

        return $this;
    }

    /**
     * @return Collection|PokemonSetMoveFour[]
     */
    public function getMovesSet4(): Collection
    {
        return $this->moves_set_4;
    }

    public function addMoveSet4(PokemonSetMoveFour $move_set_4): self
    {
        if (!$this->moves_set_4->contains($move_set_4)) {
            $this->moves_set_4[] = $move_set_4;
            $move_set_4->setPokemonSet($this);
        }

        return $this;
    }

    public function removeMoveSet4(PokemonSetMoveFour $move_set_4): self
    {
        $this->moves_set_4->removeElement($move_set_4);

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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

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
