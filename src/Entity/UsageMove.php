<?php

namespace App\Entity;

use App\Repository\UsageMoveRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UsageMoveRepository::class)
 */
class UsageMove
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TierUsage::class, inversedBy="usageMoves")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $tierUsage;

    /**
     * @ORM\ManyToOne(targetEntity=Move::class)
     * @ORM\JoinColumn(nullable=false)
	 * @Groups({"read:usage", "read:pokemon"})
     */
    private $move;

    /**
     * @ORM\Column(type="float")
	 * @Groups({"read:usage", "read:pokemon", "read:usageMove"})
     */
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

    public function getMove(): ?Move
    {
        return $this->move;
    }

    public function setMove(?Move $move): self
    {
        $this->move = $move;

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
