<?php

namespace App\Repository;

use App\Entity\PokemonInstance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonInstance|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonInstance|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonInstance[]    findAll()
 * @method PokemonInstance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonInstanceRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, PokemonInstance::class);
	}

	public function findOne($id): ?PokemonInstance
	{
		return $this->createQueryBuilder('i')
			->addSelect([
				'p', 'a', 'n', 'it', 't1', 't2',
				'm1', 'm2', 'm3', 'm4',
				'm1t', 'm2t', 'm3t', 'm4t'
			])
			->innerJoin('i.pokemon', 'p')
			->leftJoin('p.type_1', 't1')
			->leftJoin('p.type_2', 't2')
			->leftJoin('i.ability', 'a')
			->leftJoin('i.nature', 'n')
			->leftJoin('i.item', 'it')
			->leftJoin('i.move_1', 'm1')
			->leftJoin('i.move_2', 'm2')
			->leftJoin('i.move_3', 'm3')
			->leftJoin('i.move_4', 'm4')
			->leftJoin('m1.type', 'm1t')
			->leftJoin('m2.type', 'm2t')
			->leftJoin('m3.type', 'm3t')
			->leftJoin('m4.type', 'm4t')
			->andWhere('i.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();
	}
}
