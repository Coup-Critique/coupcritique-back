<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractTag;
use App\Repository\TournamentTagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TournamentTagRepository::class)
 */
class TournamentTag extends AbstractTag
{

}
