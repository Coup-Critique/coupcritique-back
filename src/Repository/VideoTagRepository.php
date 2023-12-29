<?php

namespace App\Repository;

use App\Entity\VideoTag;
use App\Repository\Abstracts\AbstractTagRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VideoTag>
 *
 * @method VideoTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method VideoTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method VideoTag[]    findAll()
 * @method VideoTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoTagRepository extends AbstractTagRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VideoTag::class);
    }
}
