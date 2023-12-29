<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait GenProperty {

    /**
     * @ORM\Column(type="integer")
     * @Groups({"read:list", "read:pokemon", "insert:pokemon", "read:ability", "read:tier", "read:item", "reac:move", "read:type"})
     */
    private int $gen;

    public function setGen(int $gen): self {
        $this->gen = $gen;
        return $this;
    }

    public function getGen(): int {
        return $this->gen;
    }
}