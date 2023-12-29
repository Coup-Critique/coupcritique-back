<?php

namespace App\Repository;

use App\Entity\UsageItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UsageItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method UsageItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method UsageItem[]    findAll()
 * @method UsageItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsageItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UsageItem::class);
    }
}
