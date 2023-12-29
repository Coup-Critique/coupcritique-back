<?php

namespace App\Repository;

use App\Entity\Replay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Replay|null find($id, $lockMode = null, $lockVersion = null)
 * @method Replay|null findOneBy(array $criteria, array $orderBy = null)
 * @method Replay[]    findAll()
 * @method Replay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReplayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Replay::class);
    }
}
