<?php

namespace App\Entity\Interfaces;

use DateTimeInterface;

interface CalendableInterface
{
    public function getStartDate(): ?DateTimeInterface;
    public function getEndDate(): ?DateTimeInterface;
}
