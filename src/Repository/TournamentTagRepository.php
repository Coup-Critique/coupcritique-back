<?php

namespace App\Repository;

use App\Entity\TournamentTag;
use App\Repository\Abstracts\AbstractTagRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TournamentTag>
 *
 * @method TournamentTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method TournamentTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method TournamentTag[]    findAll()
 * @method TournamentTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TournamentTagRepository extends AbstractTagRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TournamentTag::class);
    }
}
