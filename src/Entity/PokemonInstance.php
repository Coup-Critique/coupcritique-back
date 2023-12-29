<?php

namespace App\Entity;

use App\Repository\PokemonInstanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

// TODO remettre ce constraint en place quand import Learns fonctionnera de nouveau
/**
 * @ORM\Entity(repositoryClass=PokemonInstanceRepository::class)
 * @CustomAssert\InstancePokemonConstraint
 * @CustomAssert\InstanceAbilityConstraint
 * @CustomAssert\InstanceNatureConstraint
 * @CustomAssert\InstanceItemConstraint)
 * @CustomAssert\InstanceMoveConstraint
 */
class PokemonInstance
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read:list", "read:team", "read:list:team", "update:team"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pokemon::class)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read:list", "read:team", "read:list:team", "insert:team"})
     * @Assert\NotNull(message="Le Pokémon est manquant")
     * @Assert\Type(
     *     type="App\Entity\Pokemon",
     *     message="Le Pokémon {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $pokemon;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     */
    private $nickname;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:team", "insert:team", "insert:instance"})
     */
    private $level = 100;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:team", "insert:team", "insert:instance"})
     */
    private $shiny = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     */
    private $sex;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     */
    private $happiness = 255;

    /**
     * @ORM\ManyToOne(targetEntity=Ability::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Ability",
     *     message="Le talent {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $ability;

    /**
     * @ORM\ManyToOne(targetEntity=Nature::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Nature",
     *     message="La nature {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $nature;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Item",
     *     message="L'objet' {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $item;

    /**
     * @ORM\ManyToOne(targetEntity=Type::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Type",
     *     message="Le type {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $tera;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=31, message="Les ivs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les ivs doivent être positifs.")
     */
    private $hp_iv = 31;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=31, message="Les ivs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les ivs doivent être positifs.")
     */
    private $atk_iv = 31;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=31, message="Les ivs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les ivs doivent être positifs.")
     */
    private $def_iv = 31;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=31, message="Les ivs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les ivs doivent être positifs.")
     */
    private $spa_iv = 31;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=31, message="Les ivs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les ivs doivent être positifs.")
     */
    private $spd_iv = 31;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=31, message="Les ivs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les ivs doivent être positifs.")
     */
    private $spe_iv = 31;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=252, message="Les evs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les evs doivent être positifs.")
     */
    private $hp_ev;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=252, message="Les evs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les evs doivent être positifs.")
     */
    private $atk_ev;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=252, message="Les evs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les evs doivent être positifs.")
     */
    private $def_ev;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=252, message="Les evs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les evs doivent être positifs.")
     */
    private $spa_ev;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=252, message="Les evs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les evs doivent être positifs.")
     */
    private $spd_ev;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:team", "insert:team", "insert:instance"})
     * @Assert\LessThanOrEqual(value=252, message="Les evs doivent être inférieurs ou égales à {{ compared_value }}.")
     * @Assert\GreaterThanOrEqual(value=0, message="Les evs doivent être positifs.")
     */
    private $spe_ev;

    /**
     * Description cannot be required for PokemonSet
     * @ORM\Column(type="text", nullable=true, length=5000)
     * @Groups({"read:team", "insert:team", "update:team"})
     * @CustomAssert\TextConstraint(
     *    message="Cette description n'est pas acceptable car elle contient le ou les mots : {{ banWords }}."
     * )
     * @Assert\Length(
     *    max = 5000,
     *    maxMessage="La description peut faire au maximum 5000 caractères."
     * )
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Move::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Move",
     *     message="La capacité {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $move_1;

    /**
     * @ORM\ManyToOne(targetEntity=Move::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Move",
     *     message="La capacité {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $move_2;

    /**
     * @ORM\ManyToOne(targetEntity=Move::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Move",
     *     message="La capacité {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $move_3;

    /**
     * @ORM\ManyToOne(targetEntity=Move::class)
     * @Groups({"read:team", "insert:team"})
     * @Assert\Type(
     *     type="App\Entity\Move",
     *     message="La capacité {{ value }} n'existe pas"
     * )
     * @Assert\Valid
     */
    private $move_4;

    /**
     * Used as a OneToOne
     * @ORM\ManyToOne(targetEntity=Team::class)
     * @Groups({"insert:team"})
     * @var Team $team
     */
    private $team;

    /**
     * Used as a OneToOne
     * @ORM\ManyToOne(targetEntity=PokemonSet::class)
     * @Groups({"insert:team"})
     * @var PokemonSet $pokemon_set
     */
    private $pokemon_set;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getShiny(): ?bool
    {
        return $this->shiny;
    }

    public function setShiny(?bool $shiny): self
    {
        $this->shiny = $shiny;

        return $this;
    }

    public function getSex(): ?bool
    {
        return $this->sex;
    }

    public function setSex(?bool $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getHappiness(): ?int
    {
        return $this->happiness;
    }

    public function setHappiness(?int $happiness): self
    {
        $this->happiness = $happiness;

        return $this;
    }

    public function getAbility(): ?Ability
    {
        return $this->ability;
    }

    public function setAbility(?Ability $ability): self
    {
        $this->ability = $ability;

        return $this;
    }

    public function getNature(): ?Nature
    {
        return $this->nature;
    }

    public function setNature(?Nature $nature): self
    {
        $this->nature = $nature;

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

    public function getTera(): ?Type
    {
        return $this->tera;
    }

    public function setTera(?Type $type): self
    {
        $this->tera = $type;

        return $this;
    }

    public function getHpIv(): ?int
    {
        return $this->hp_iv;
    }

    public function setHpIv(?int $hp_iv): self
    {
        $this->hp_iv = $hp_iv;

        return $this;
    }

    public function getAtkIv(): ?int
    {
        return $this->atk_iv;
    }

    public function setAtkIv(?int $atk_iv): self
    {
        $this->atk_iv = $atk_iv;

        return $this;
    }

    public function getDefIv(): ?int
    {
        return $this->def_iv;
    }

    public function setDefIv(?int $def_iv): self
    {
        $this->def_iv = $def_iv;

        return $this;
    }

    public function getSpaIv(): ?int
    {
        return $this->spa_iv;
    }

    public function setSpaIv(?int $spa_iv): self
    {
        $this->spa_iv = $spa_iv;

        return $this;
    }

    public function getSpdIv(): ?int
    {
        return $this->spd_iv;
    }

    public function setSpdIv(?int $spd_iv): self
    {
        $this->spd_iv = $spd_iv;

        return $this;
    }

    public function getSpeIv(): ?int
    {
        return $this->spe_iv;
    }

    public function setSpeIv(?int $spe_iv): self
    {
        $this->spe_iv = $spe_iv;

        return $this;
    }

    public function getHpEv(): ?int
    {
        return $this->hp_ev;
    }

    public function setHpEv(?int $hp_ev): self
    {
        $this->hp_ev = $hp_ev;

        return $this;
    }

    public function getAtkEv(): ?int
    {
        return $this->atk_ev;
    }

    public function setAtkEv(?int $atk_ev): self
    {
        $this->atk_ev = $atk_ev;

        return $this;
    }

    public function getDefEv(): ?int
    {
        return $this->def_ev;
    }

    public function setDefEv(?int $def_ev): self
    {
        $this->def_ev = $def_ev;

        return $this;
    }

    public function getSpaEv(): ?int
    {
        return $this->spa_ev;
    }

    public function setSpaEv(?int $spa_ev): self
    {
        $this->spa_ev = $spa_ev;

        return $this;
    }

    public function getSpdEv(): ?int
    {
        return $this->spd_ev;
    }

    public function setSpdEv(?int $spd_ev): self
    {
        $this->spd_ev = $spd_ev;

        return $this;
    }

    public function getSpeEv(): ?int
    {
        return $this->spe_ev;
    }

    public function setSpeEv(?int $spe_ev): self
    {
        $this->spe_ev = $spe_ev;

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

    public function getMove1(): ?Move
    {
        return $this->move_1;
    }

    public function setMove1(?Move $move_1): self
    {
        $this->move_1 = $move_1;

        return $this;
    }

    public function getMove2(): ?Move
    {
        return $this->move_2;
    }

    public function setMove2(?Move $move_2): self
    {
        $this->move_2 = $move_2;

        return $this;
    }

    public function getMove3(): ?Move
    {
        return $this->move_3;
    }

    public function setMove3(?Move $move_3): self
    {
        $this->move_3 = $move_3;

        return $this;
    }

    public function getMove4(): ?Move
    {
        return $this->move_4;
    }

    public function setMove4(?Move $move_4): self
    {
        $this->move_4 = $move_4;

        return $this;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setPokemonSet(?PokemonSet $pokemon_set): self
    {
        $this->pokemon_set = $pokemon_set;

        return $this;
    }

    public function getPokemonSet(): ?PokemonSet
    {
        return $this->pokemon_set;
    }

    public function getGen(): ?int
    {
        if ($this->team) {
            return $this->team->getGen();
        }
        if ($this->pokemon_set) {
            return $this->pokemon_set->getGen();
        }
        return null;
    }
}
