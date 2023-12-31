<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractTag;
use App\Repository\ActualityTagRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActualityTagRepository::class)]
class ActualityTag extends AbstractTag
{
}
