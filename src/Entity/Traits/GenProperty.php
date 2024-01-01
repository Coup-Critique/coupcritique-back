<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

trait GenProperty
{
    #[Groups([
        "read:list",
        "read:pokemon",
        "insert:pokemon",
        "read:ability",
        "read:tier",
        "read:item",
        "reac:move",
        "read:type"
    ])]
    #[ORM\Column(type: 'integer')]
    private int $gen;

    public function setGen(int $gen): self
    {
        $this->gen = $gen;
        return $this;
    }

    public function getGen(): int
    {
        return $this->gen;
    }
}
