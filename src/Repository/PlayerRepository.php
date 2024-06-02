<?php

namespace App\Repository;

use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Player>
 *
 * @method Player|null find($id, $lockMode = null, $lockVersion = null)
 * @method Player|null findOneBy(array $criteria, array $orderBy = null)
 * @method Player[]    findAll()
 * @method Player[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.points', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findTopPlayers()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.points', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }
}
