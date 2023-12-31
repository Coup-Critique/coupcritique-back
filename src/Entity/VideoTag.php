<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractTag;
use App\Repository\VideoTagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VideoTagRepository::class)]
class VideoTag extends AbstractTag
{
}
