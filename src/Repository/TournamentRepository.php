<?php

namespace App\Repository;

use App\Entity\Tournament;
use App\Repository\Abstracts\AbstractArticleRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TournamentRepository extends AbstractArticleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }
}
