<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractVideo;
use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: VideoRepository::class)]
class Video extends AbstractVideo
{
    #[ORM\ManyToMany(targetEntity: VideoTag::class)]
    #[Groups(['read:video', 'read:list', 'insert:video', 'update:video'])]
    protected $tags;
}
