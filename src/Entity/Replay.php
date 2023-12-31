<?php

namespace App\Entity;

use App\Repository\ReplayRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReplayRepository::class)]
class Replay
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['read:team', 'insert:team'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['read:team', 'insert:team', 'update:team'])]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9_-]*$/', message: 'Le mot de passe doit comporter 8 caractères dont au moins 1 majuscule, 1 minuscule, 1 chiffre et un caractère spécial.')]
    #[Assert\Length(max: 255, maxMessage: 'La description peut faire au maximum 255 caractères.')]
    private $uri;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'replays')]
    #[ORM\JoinColumn(nullable: false)]
    private $team;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(?string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(?Team $team): self
    {
        $this->team = $team;

        return $this;
    }
}
