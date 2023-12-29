<?php

namespace App\Repository;

use App\Entity\PokemonSetMoveOne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonSetMoveOne|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSetMoveOne|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSetMoveOne[]    findAll()
 * @method PokemonSetMoveOne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSetMoveOneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonSetMoveOne::class);
    }
}
