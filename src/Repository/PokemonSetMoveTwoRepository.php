<?php

namespace App\Repository;

use App\Entity\PokemonSetMoveTwo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonSetMoveTwo|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSetMoveTwo|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSetMoveTwo[]    findAll()
 * @method PokemonSetMoveTwo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSetMoveTwoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonSetMoveTwo::class);
    }
}
