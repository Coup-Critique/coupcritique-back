<?php

namespace App\Repository;

use App\Entity\Video;
use App\Repository\Abstracts\AbstractVideoRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends AbstractVideoRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }
}
