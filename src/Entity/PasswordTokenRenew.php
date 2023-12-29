<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractCcToken;
use App\Repository\PasswordTokenRenewRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PasswordTokenRenewRepository::class)
 */
class PasswordTokenRenew extends AbstractCcToken
{
    public function isTokenValid(): bool
    {
        return $this->token_date_creation->diff(new \DateTime())->days < 1;
    }
}
