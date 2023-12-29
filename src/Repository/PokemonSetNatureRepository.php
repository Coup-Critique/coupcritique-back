<?php

namespace App\Repository;

use App\Entity\PokemonSetNature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonSetNature|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSetNature|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSetNature[]    findAll()
 * @method PokemonSetNature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSetNatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonSetNature::class);
    }
}
