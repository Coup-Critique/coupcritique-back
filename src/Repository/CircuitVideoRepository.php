<?php

namespace App\Repository;

use App\Entity\CircuitVideo;
use App\Repository\Abstracts\AbstractVideoRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CircuitVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CircuitVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CircuitVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CircuitVideoRepository extends AbstractVideoRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CircuitVideo::class);
    }
}
