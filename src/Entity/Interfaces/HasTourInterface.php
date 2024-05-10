<?php

namespace App\Entity\Interfaces;

use App\Entity\CircuitTour;

interface HasTourInterface
{
    public function getTour(): ?CircuitTour;
    public function setTour(?CircuitTour $tour): self;
}
