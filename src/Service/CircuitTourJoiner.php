<?php

namespace App\Service;

use App\Entity\Interfaces\HasTourInterface;
use App\Repository\CircuitTourRepository;

class CircuitTourJoiner
{
    public function __construct(private CircuitTourRepository $ctRepo)
    {
    }

    public function joinTour(HasTourInterface $el, string $json): void
    {
        if ($el->getTour()?->getId()) return;

        $articleJson = json_decode($json);
        if (empty($articleJson->tour)) {
            $el->setTour(null);
            return;
        }

        $tourTitle = $articleJson->tour;

        $tour = $this->ctRepo->findOneByTitle($tourTitle);
        if ($tour) {
            $el->setTour($tour);
        } else {
            $el->setTour(null);
            throw new \Exception("Tour $tourTitle not found");
        }
    }
}
