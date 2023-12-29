<?php

namespace App\Repository;

use App\Entity\ActualityTag;
use App\Repository\Abstracts\AbstractTagRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActualityTag>
 *
 * @method ActualityTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActualityTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActualityTag[]    findAll()
 * @method ActualityTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActualityTagRepository extends AbstractTagRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActualityTag::class);
    }
}
