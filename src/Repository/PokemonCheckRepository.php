<?php

namespace App\Repository;

use App\Entity\PokemonCheck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonCheck|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonCheck|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonCheck[]    findAll()
 * @method PokemonCheck[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonCheckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonCheck::class);
    }
}
