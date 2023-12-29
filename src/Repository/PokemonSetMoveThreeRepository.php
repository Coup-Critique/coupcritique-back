<?php

namespace App\Repository;

use App\Entity\PokemonSetMoveThree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonSetMoveThree|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSetMoveThree|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSetMoveThree[]    findAll()
 * @method PokemonSetMoveThree[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSetMoveThreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonSetMoveThree::class);
    }
}
