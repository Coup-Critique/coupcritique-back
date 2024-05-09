<?php

namespace App\Repository;

use App\Entity\CircuitArticle;
use App\Repository\Abstracts\AbstractArticleRepository;
use Doctrine\Persistence\ManagerRegistry;

class CircuitArticleRepository extends AbstractArticleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CircuitArticle::class);
    }
}
