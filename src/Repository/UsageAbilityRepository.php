<?php

namespace App\Repository;

use App\Entity\UsageAbility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsageAbility|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsageAbility|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsageAbility[]    findAll()
 * @method UsageAbility[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsageAbilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsageAbility::class);
    }
}
