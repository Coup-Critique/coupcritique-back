<?php

namespace App\Entity;

use App\Repository\UsageSpreadRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UsageSpreadRepository::class)]
class UsageSpread
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: TierUsage::class, inversedBy: 'usageSpreads')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $tierUsage;

    #[ORM\ManyToOne(targetEntity: Nature::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['read:usage'])]
    private $nature;

    #[ORM\Column(type: 'json')]
    #[Groups(['read:usage'])]
    private $evs = [];

    #[ORM\Column(type: 'float')]
    #[Groups(['read:usage', 'read:usageSpread'])]
    private $percent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTierUsage(): ?TierUsage
    {
        return $this->tierUsage;
    }

    public function setTierUsage(?TierUsage $tierUsage): self
    {
        $this->tierUsage = $tierUsage;

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

    public function getEvs(): ?array
    {
        return $this->evs;
    }

    public function setEvs(?array $evs): self
    {
        $this->evs = $evs;

        return $this;
    }

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function setPercent(float $percent): self
    {
        $this->percent = $percent;

        return $this;
    }
}
