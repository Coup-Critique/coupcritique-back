<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use App\Entity\Interfaces\CommentParentInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;

/**
 * @CustomAssert\TeamInstanceAndTierConstraint
 * @CustomAssert\TeamTagConstraint
 */
#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team implements CommentParentInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:team', 'read:list', 'read:list:team'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:team', 'read:list', 'read:list:team'])]
    private $user;

    /**
     * @CustomAssert\TextConstraint(
     *    message="Ce nom n'est pas acceptable car il contient le ou les mots : {{ banWords }}."
     * )
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    #[Assert\NotNull(message: "Le nom de l'équipe est requis")]
    #[Assert\Length(max: 50, maxMessage: "Le nom de l'équipe peut faire au maximum 50 caractères.")]
    private $name;

    /**
     * @CustomAssert\TextConstraint(
     *    message="Cette description n'est pas acceptable car elle contient le ou les mots : {{ banWords }}."
     * )
     */
    #[ORM\Column(type: 'text', length: 5000, nullable: true)]
    #[Groups(['read:team', 'insert:team', 'update:team'])]
    #[Assert\NotNull(message: 'La description est requise')]
    #[Assert\Length(max: 5000, maxMessage: 'La description peut faire au maximum 5000 caractères.')]
    private $description;

    #[ORM\Column(type: 'text', length: 500)]
    #[Groups(['read:team', 'insert:team'])]
    #[Assert\NotNull(message: "L'export de l'équipe par pokemonshowdown est requis")]
    private $export;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:team', 'read:list', 'read:list:team'])]
    private $date_creation;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['read:team'])]
    private $update_date;

    #[ORM\OneToOne(targetEntity: PokemonInstance::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    #[Assert\NotNull(message: "Il doit y avoir au moins un Pokémon dans l'équipe")]
    #[Assert\Valid]
    private $pkm_inst_1;

    #[ORM\OneToOne(targetEntity: PokemonInstance::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    #[Assert\Valid]
    private $pkm_inst_2;

    #[ORM\OneToOne(targetEntity: PokemonInstance::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    #[Assert\Valid]
    private $pkm_inst_3;

    #[ORM\OneToOne(targetEntity: PokemonInstance::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    #[Assert\Valid]
    private $pkm_inst_4;

    #[ORM\OneToOne(targetEntity: PokemonInstance::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    #[Assert\Valid]
    private $pkm_inst_5;

    #[ORM\OneToOne(targetEntity: PokemonInstance::class, cascade: ['persist', 'remove'])]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    #[Assert\Valid]
    private $pkm_inst_6;

    #[ORM\ManyToOne(targetEntity: Tier::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team'])]
    #[Assert\NotNull(message: 'Le tier est requis.')]
    #[Assert\Valid]
    private $tier;

    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team', 'update:team'])]
    private $tags;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:team', 'read:list', 'read:list:team'])]
    private $certified;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:team', 'read:team:admin', 'read:list', 'read:list:team'])]
    private $banned;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['read:team'])]
    private $top_week;

    #[ORM\Column(type: 'string', length: 17, nullable: true)]
    #[Groups(['read:team', 'insert:team', 'update:team'])]
    #[Assert\Length(min: 6, max: 17, exactMessage: "L'id équipe doit faire {{ limit }} caractères.")]
    private $team_id;

    #[ORM\OneToMany(targetEntity: Replay::class, mappedBy: 'team', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    #[Assert\Count(max: 5, maxMessage: 'Vous ne pouvez pas ajouter plus de {{ limit }} replays.')]
    #[Groups(['read:team', 'insert:team', 'update:team'])]
    private $replays;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['read:team:admin'])]
    private $history;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favorites')]
    private $enjoyers;

    /**
     * @var bool|null $isOwnUserFavorite
     */
    #[Groups(['read:team', 'read:list', 'read:list:team'])]
    private $isOwnUserFavorite = false;

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'team', cascade: ['persist', 'remove'])]
    private $comments;

    /**
     * @CustomAssert\GenConstraint()
     */
    #[ORM\Column(type: 'smallint')]
    #[Groups(['read:team', 'read:list', 'read:list:team', 'insert:team'])]
    #[Assert\NotNull(message: 'La génération est requise.')]
    private $gen;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->replays = new ArrayCollection();
        $this->enjoyers = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    public function getExport(): ?string
    {
        return $this->export;
    }

    public function setExport(?string $export): self
    {
        $this->export = $export;

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

    public function setUpdateDate(\DateTimeInterface $update_date): self
    {
        $this->update_date = $update_date;

        return $this;
    }

    public function getPkmInst1(): ?PokemonInstance
    {
        return $this->pkm_inst_1;
    }

    public function setPkmInst1(?PokemonInstance $pkm_inst_1): self
    {
        $this->pkm_inst_1 = $pkm_inst_1;
        if (!is_null($pkm_inst_1)) {
            $pkm_inst_1->setTeam($this);
        }

        return $this;
    }

    public function getPkmInst2(): ?PokemonInstance
    {
        return $this->pkm_inst_2;
    }

    public function setPkmInst2(?PokemonInstance $pkm_inst_2): self
    {
        $this->pkm_inst_2 = $pkm_inst_2;
        if (!is_null($pkm_inst_2)) {
            $pkm_inst_2->setTeam($this);
        }

        return $this;
    }

    public function getPkmInst3(): ?PokemonInstance
    {
        return $this->pkm_inst_3;
    }

    public function setPkmInst3(?PokemonInstance $pkm_inst_3): self
    {
        $this->pkm_inst_3 = $pkm_inst_3;
        if (!is_null($pkm_inst_3)) {
            $pkm_inst_3->setTeam($this);
        }

        return $this;
    }

    public function getPkmInst4(): ?PokemonInstance
    {
        return $this->pkm_inst_4;
    }

    public function setPkmInst4(?PokemonInstance $pkm_inst_4): self
    {
        $this->pkm_inst_4 = $pkm_inst_4;
        if (!is_null($pkm_inst_4)) {
            $pkm_inst_4->setTeam($this);
        }

        return $this;
    }

    public function getPkmInst5(): ?PokemonInstance
    {
        return $this->pkm_inst_5;
    }

    public function setPkmInst5(?PokemonInstance $pkm_inst_5): self
    {
        $this->pkm_inst_5 = $pkm_inst_5;
        if (!is_null($pkm_inst_5)) {
            $pkm_inst_5->setTeam($this);
        }

        return $this;
    }

    public function getPkmInst6(): ?PokemonInstance
    {
        return $this->pkm_inst_6;
    }

    public function setPkmInst6(?PokemonInstance $pkm_inst_6): self
    {
        $this->pkm_inst_6 = $pkm_inst_6;
        if (!is_null($pkm_inst_6)) {
            $pkm_inst_6->setTeam($this);
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

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function getCertified(): ?bool
    {
        return $this->certified;
    }

    public function setCertified(?bool $certified): self
    {
        $this->certified = $certified;

        return $this;
    }

    public function getBanned(): ?bool
    {
        return $this->banned;
    }

    public function setBanned(?bool $banned): self
    {
        $this->banned = $banned;

        return $this;
    }

    public function getTopWeek(): ?\DateTimeInterface
    {
        return $this->top_week;
    }

    public function setTopWeek(?\DateTimeInterface $top_week): self
    {
        $this->top_week = $top_week;

        return $this;
    }

    public function getTeamId(): ?string
    {
        return $this->team_id;
    }

    public function setTeamId(?string $team_id): self
    {
        $this->team_id = $team_id;

        return $this;
    }

    /**
     * @return Collection|Replay[]
     */
    public function getReplays(): Collection
    {
        return $this->replays;
    }

    public function addReplay(Replay $replay): self
    {
        if (!$this->replays->contains($replay)) {
            $this->replays[] = $replay;
            $replay->setTeam($this);
        }

        return $this;
    }

    public function removeReplay(Replay $replay): self
    {
        if ($this->replays->removeElement($replay)) {
            // set the owning side to null (unless already changed)
            if ($replay->getTeam() === $this) {
                $replay->setTeam(null);
            }
        }

        return $this;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): self
    {
        $this->history = $history;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getEnjoyers(): Collection
    {
        return $this->enjoyers;
    }

    public function IsOwnUserFavorite()
    {
        return $this->isOwnUserFavorite;
    }

    public function setIsOwnUserFavorite($user)
    {
        $this->isOwnUserFavorite = $this->getEnjoyers()->contains($user);
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTeam($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTeam() === $this) {
                $comment->setTeam(null);
            }
        }

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

    public function getPokemonInstances(): Collection
    {
        $collection = new ArrayCollection();

        for ($index = 1; $index < 7 && !is_null($this->{"pkm_inst_$index"}); $index++)
            $collection->add($this->{"pkm_inst_$index"});

        return $collection;
    }
}
