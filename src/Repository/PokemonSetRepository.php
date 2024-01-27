<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\PokemonSet;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PokemonSet|null find($id, $lockMode = null, $lockVersion = null)
 * @method PokemonSet|null findOneBy(array $criteria, array $orderBy = null)
 * @method PokemonSet[]    findAll()
 * @method PokemonSet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonSetRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, PokemonSet::class);
	}

	public function insert(PokemonSet $pokemonSet, User $user)
	{
		$pokemonSet->setDateCreation(new \DateTime());
		$pokemonSet->setUser($user);
		$this->_em->persist($pokemonSet);
		$this->_em->flush();
		return $pokemonSet;
	}

	public function update(PokemonSet $pokemonSet, User $user)
	{
		$pokemonSet->setUpdateDate(new \DateTime());
		if (empty($pokemonSet->getUser())) {
			$pokemonSet->setUser($user);
		}
		$this->_em->flush();
		return $pokemonSet;
	}

	public function delete(PokemonSet $pokemonSet): void
	{
		$this->_em->remove($pokemonSet);
		$this->_em->flush();
	}

	public function baseQuery()
	{
		return $this->createQueryBuilder('ps')
			->select([
				'ps', 'i', 't',
				'psi', 'pst', 'psa', 'psn',
				'psm1', 'psm2', 'psm3', 'psm4'
			])
			->innerJoin('ps.instance', 'i')
			->leftJoin('ps.tier', 't')
			->leftJoin('ps.items_set', 'psi')
			->leftJoin('ps.teras_set', 'pst')
			->leftJoin('ps.abilities_set', 'psa')
			->leftJoin('ps.natures_set', 'psn')
			->leftJoin('ps.moves_set_1', 'psm1')
			->leftJoin('ps.moves_set_2', 'psm2')
			->leftJoin('ps.moves_set_3', 'psm3')
			->leftJoin('ps.moves_set_4', 'psm4')
			->orderBy('ps.id', 'ASC')
			->addOrderBy('psi.rank', 'ASC')
			->addOrderBy('psa.rank', 'ASC')
			->addOrderBy('psn.rank', 'ASC')
			->addOrderBy('psm1.rank', 'ASC')
			->addOrderBy('psm2.rank', 'ASC')
			->addOrderBy('psm3.rank', 'ASC')
			->addOrderBy('psm4.rank', 'ASC');
	}

	public function findOneById($id): ?PokemonSet
	{
		return $this->baseQuery()
			->where('ps.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * @return array
	 *
	 * Custom for double join select
	 */
	public function findByPokemon(Pokemon $pokemon): array
	{
		return $this->baseQuery()
			->addSelect(['-t.sortOrder AS HIDDEN inverse_order'])
			->innerJoin('i.pokemon', 'p')
			->where('p.id = :pid')
			->andWhere('t.playable = 1')
			->setParameter('pid', $pokemon->getId())
			->addOrderBy('inverse_order', 'DESC')
			->getQuery()
			->getResult();
	}
}
