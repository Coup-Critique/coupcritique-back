<?php

namespace App\Repository;

use App\Entity\PokemonMove;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonMove|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonMove|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonMove[]    findAll()
 * @method PokemonMove[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonMoveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonMove::class);
    }
}
