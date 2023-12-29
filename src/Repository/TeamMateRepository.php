<?php

namespace App\Repository;

use App\Entity\TeamMate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TeamMate|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamMate|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamMate[]    findAll()
 * @method TeamMate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamMateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMate::class);
    }
}
