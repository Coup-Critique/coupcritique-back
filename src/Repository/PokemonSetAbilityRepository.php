<?php

namespace App\Repository;

use App\Entity\PokemonSetAbility;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonSetAbility|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSetAbility|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSetAbility[]    findAll()
 * @method PokemonSetAbility[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSetAbilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokemonSetAbility::class);
    }
}
