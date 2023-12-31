<?php

namespace App\Entity\Abstracts;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

abstract class AbstractCcToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected $user;

    #[ORM\Column(type: 'string', length: 255)]
    protected $token;

    #[ORM\Column(type: 'datetime')]
    protected ?\Datetime $token_date_creation = null;

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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function createToken(User $user): self
    {
        // Generate random bytes by openSSL -> more secure than simply hashing
		$token = openssl_random_pseudo_bytes(20);
        $token = bin2hex($token); // convert bytes (binary) to hexadecimal
        $this->token = $token;
        $this->token_date_creation = new \DateTime;
        $this->user = $user;

        return $this;
    }

    public function setTokenDateCreation(?\DateTimeInterface $datetime): self
    {
        $this->token_date_creation = $datetime;
        return $this;
    }

    public function getTokenDateCreation(): ?\DateTime
    {
        return $this->token_date_creation;
    }

    abstract public function isTokenValid(): bool;
}
