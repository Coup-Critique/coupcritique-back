<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractTag;
use App\Repository\GuideTagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=GuideTagRepository::class)
 */
class GuideTag extends AbstractTag
{
}
