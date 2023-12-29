<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
	 * @Groups({"read:list"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user = null;
    
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=true)
	 * @Groups({"read:list"})
     */
    private ?User $notifier = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
	 * @Groups({"read:list"})
     */

    private ?string $subject = null;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
	 * @Groups({"read:list"})
     */

    private ?string $content = null;

    /**
     * @ORM\Column(type="datetime")
	 * @Groups({"read:list"})
     */
    private ?\Datetime $date = null;

    /**
     * @ORM\Column(type="boolean")
	 * @Groups({"read:list"})
     */
    private bool $viewed = false;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
	 * @Groups({"read:list"})
     */
    private $entityName;

    /**
     * @ORM\Column(type="integer", nullable=true)
	 * @Groups({"read:list"})
     */
    private $entityId;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
	 * @Groups({"read:list"})
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
	 * @Groups({"read:list"})
     */
    private $icon;

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
    
    public function getNotifier(): ?User
    {
        return $this->notifier;
    }

    public function setNotifier(?User $notifier): self
    {
        $this->notifier = $notifier;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

        return $this;
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getViewed(): ?bool
    {
        return $this->viewed;
    }

    public function setViewed(bool $viewed): self
    {
        $this->viewed = $viewed;

        return $this;
    }

    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(?string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
    
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }
}
