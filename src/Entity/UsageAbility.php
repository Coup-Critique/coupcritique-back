<?php

namespace App\Entity;

use App\Repository\UsageAbilityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UsageAbilityRepository::class)
 */
class UsageAbility
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TierUsage::class, inversedBy="usageAbilities")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $tierUsage;

    /**
     * @ORM\ManyToOne(targetEntity=Ability::class)
     * @ORM\JoinColumn(nullable=false)
	 * @Groups({"read:usage", "read:pokemon"})
     */
    private $ability;

    /**
     * @ORM\Column(type="float")
	 * @Groups({"read:usage", "read:pokemon", "read:usageAbility"})
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

    public function getAbility(): ?Ability
    {
        return $this->ability;
    }

    public function setAbility(?Ability $ability): self
    {
        $this->ability = $ability;

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
