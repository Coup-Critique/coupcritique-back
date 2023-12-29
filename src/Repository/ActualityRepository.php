<?php

namespace App\Repository;

use App\Entity\Actuality;
use App\Repository\Abstracts\AbstractArticleRepository;
use Doctrine\Persistence\ManagerRegistry;

class ActualityRepository extends AbstractArticleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Actuality::class);
    }
}
