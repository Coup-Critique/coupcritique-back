<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:player', 'read:list'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read:player', 'read:list'])]
    #[Assert\Length(max: 50, maxMessage: 'Le pseudo showdown peut faire au maximum 50 caractères.')]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    #[Groups(['read:player', 'read:list'])]
    #[Assert\Length(max: 50, maxMessage: 'Le pseudo discord peut faire au maximum 50 caractères.')]
    private ?string $discord_name = null;

    #[ORM\ManyToOne(inversedBy: 'players', targetEntity: User::class)]
    #[Groups(['read:player'])]
    private ?User $user = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['read:player', 'read:list'])]
    private int $points = 0;

    #[ORM\Column(type: 'float')]
    #[Groups(['read:player', 'read:list'])]
    private float $prize = 0;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['read:player', 'read:list'])]
    #[Assert\Length(max: 255, maxMessage: 'Le titre peut faire au maximum 255 caractères.')]
    protected $title;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDiscordName(): ?string
    {
        return $this->discord_name;
    }

    public function setDiscordName(?string $discord_name): self
    {
        $this->discord_name = $discord_name;

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

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getPrize(): ?float
    {
        return $this->prize;
    }

    public function setPrize(?float $prize): self
    {
        $this->prize = $prize;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
