<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractCcToken;
use App\Repository\ActivateUserTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivateUserTokenRepository::class)]
class ActivateUserToken extends AbstractCcToken
{
    public function isTokenValid(): bool
    {
        return $this->token_date_creation->diff(new \DateTime())->days < 2;
    }
}
