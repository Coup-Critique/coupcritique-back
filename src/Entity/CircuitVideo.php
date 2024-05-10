<?php

namespace App\Entity;

use App\Entity\Abstracts\AbstractVideo;
use App\Entity\Interfaces\HasTourInterface;
use App\Repository\CircuitVideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CircuitVideoRepository::class)]
class CircuitVideo extends AbstractVideo implements HasTourInterface
{
    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[Groups(['read:video', 'insert:video', 'update:video'])]
    private ?CircuitTour $tour = null;

    #[ORM\ManyToMany(targetEntity: VideoTag::class)]
    #[Groups(['read:video', 'read:list', 'insert:video', 'update:video'])]
    protected $tags;

    public function getTour(): ?CircuitTour
    { 
        return $this->tour;
    }

    public function setTour(?CircuitTour $tour): self
    {
        $this->tour = $tour;

        return $this;
    }
}
