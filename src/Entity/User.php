<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['username'], message: 'Ce pseudo est déjà utilisé')]
#[UniqueEntity(fields: ['email'], message: 'Cette adresse mail est déjà utilisée')]
class User implements UserInterface, LegacyPasswordAuthenticatedUserInterface
{
    final public const ROLE_USER  = 'ROLE_USER';
    final public const ROLE_ADMIN = 'ROLE_ADMIN';
    final public const ROLE_MODO  = 'ROLE_MODO';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:user', 'read:list', 'read:name', 'read:team', 'read:list:team'])]
    private $id;

    #[CustomAssert\TextConstraint()]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['read:user', 'insert:user', 'read:list', 'read:name', 'read:team', 'read:list:team'])]
    #[Assert\Length(max: 180, maxMessage: 'Le nom utilisateur peut faire au maximum 180 caractères.')]
    private $username;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['read:user:own', 'read:user:admin', 'insert:user'])]
    #[Assert\Length(max: 180, maxMessage: "L'email peut faire au maximum 180 caractères.")]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private ?bool $is_admin = null;

    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private ?bool $is_modo = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private ?bool $is_tiper = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private ?bool $is_winner = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private ?bool $is_weeker = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private ?bool $is_certified = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private ?bool $is_content_creator = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['insert:user'])]
    #[Assert\Length(max: 255, maxMessage: 'Le mot de passe peut faire au maximum 255 caractères.')]
    private string $password;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['read:user:admin'])]
    private $banned;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['read:user:admin'])]
    private $deleted;

    #[ORM\Column(type: 'boolean')]
    #[Groups('user:read:to_admin')]
    private $activated;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['read:user', 'read:user:admin'])]
    private $date_creation;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read:user', 'insert:user', 'read:list'])]
    #[Assert\Length(max: 50, maxMessage: 'Le pseudo discord peut faire au maximum 50 caractères.')]
    private $discord_name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read:user', 'read:list', 'read:team', 'read:list:team'])]
    private $picture;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read:user', 'insert:user', 'read:list'])]
    #[Assert\Length(max: 50, maxMessage: 'Le pseudo showdown peut faire au maximum 50 caractères.')]
    private $showdown_name;

    #[ORM\Column(type: 'text', nullable: true, length: 1000)]
    #[Groups(['read:user:admin'])]
    private $history;

    #[ORM\ManyToMany(targetEntity: Team::class, inversedBy: 'enjoyers')]
    private $favorites;

    #[ORM\Column(type: 'json')]
    private $ips = [];

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Player::class)]
    private Collection $players;

    public function __construct()
    {
        $this->favorites = new ArrayCollection();
        $this->players = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * returns the identifier for this user (e.g. its username or email address)
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        $this->is_admin = in_array(self::ROLE_ADMIN, $roles);
        $this->is_modo = $this->is_admin ?: in_array(self::ROLE_MODO, $roles);

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getBanned(): ?bool
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): self
    {
        $this->banned = $banned;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getActivated(): ?bool
    {
        return $this->activated;
    }

    public function setActivated(bool $activated): self
    {
        $this->activated = $activated;

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

    public function getDiscordName(): ?string
    {
        return $this->discord_name;
    }

    public function setDiscordName(?string $discord_name): self
    {
        $this->discord_name = $discord_name;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    public function getShowdownName(): ?string
    {
        return $this->showdown_name;
    }

    public function setShowdownName(?string $showdown_name): self
    {
        $this->showdown_name = $showdown_name;

        return $this;
    }

    public function hasPasswordToken(): bool
    {
        // return !is_null($this->password_token_refresh);
        return false;
    }

    public function getIsAdmin(): ?bool
    {
        if (is_null($this->is_admin)) {
            $this->is_admin = in_array(self::ROLE_ADMIN, $this->getRoles());
        }
        return $this->is_admin;
    }

    public function setIsAdmin(bool $is_admin): self
    {
        $this->is_admin = $is_admin;

        return $this;
    }

    public function getIsModo(): ?bool
    {
        if (is_null($this->is_modo)) {
            $this->is_modo = $this->getIsAdmin() ?: in_array(self::ROLE_MODO, $this->getRoles());
        }
        return $this->is_modo;
    }

    public function setIsModo(bool $is_modo): self
    {
        $this->is_modo = $is_modo;

        return $this;
    }

    public function getIsTiper(): ?bool
    {
        return $this->is_tiper;
    }

    public function setIsTiper(bool $is_tiper): self
    {
        $this->is_tiper = $is_tiper;

        return $this;
    }

    public function getIsWinner(): ?bool
    {
        return $this->is_winner;
    }

    public function setIsWinner(bool $is_winner): self
    {
        $this->is_winner = $is_winner;

        return $this;
    }

    public function getIsWeeker(): ?bool
    {
        return $this->is_weeker;
    }

    public function setIsWeeker(bool $is_weeker): self
    {
        $this->is_weeker = $is_weeker;

        return $this;
    }

    public function getIsCertified(): ?bool
    {
        return $this->is_certified;
    }

    public function setIsCertified(bool $is_certified): self
    {
        $this->is_certified = $is_certified;

        return $this;
    }

    public function getIsContentCreator(): ?bool
    {
        return $this->is_content_creator;
    }

    public function setIsContentCreator(bool $is_content_creator): self
    {
        $this->is_content_creator = $is_content_creator;

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
     * @return Collection|Team[]
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Team $team): self
    {
        if (!$this->favorites->contains($team)) {
            $this->favorites[] = $team;
        }

        return $this;
    }

    public function removeFavorite(Team $team): self
    {
        $this->favorites->removeElement($team);

        return $this;
    }

    public function setIps(?array $ips): self
    {
        $this->ips = $ips;
        return $this;
    }

    public function addIp(string $ip): self
    {
        if (is_null($this->ips)) {
            $this->ips = [];
        }
        if (!in_array($ip, $this->ips)) {
            $this->ips[] = $ip;
        }
        return $this;
    }

    public function getIps(): ?array
    {
        return $this->ips;
    }

    /**
     * @return Collection<int, Player>
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }

    public function addPlayer(Player $player): static
    {
        if (!$this->players->contains($player)) {
            $this->players->add($player);
            $player->setUser($this);
        }

        return $this;
    }

    public function removePlayer(Player $player): static
    {
        if ($this->players->removeElement($player)) {
            // set the owning side to null (unless already changed)
            if ($player->getUser() === $this) {
                $player->setUser(null);
            }
        }

        return $this;
    }
}
