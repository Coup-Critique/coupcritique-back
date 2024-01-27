<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\Tier;
use App\Entity\TierUsage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Cache;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TierUsage|null find($id, $lockMode = null, $lockVersion = null)
 * @method TierUsage|null findOneBy(array $criteria, array $orderBy = null)
 * @method TierUsage[]    findAll()
 * @method TierUsage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TierUsageRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, TierUsage::class);
	}

	/**
	 * @return TierUsage
	 */
	public function insert(TierUsage $tierUsage)
	{
		$this->_em->persist($tierUsage);
		$this->_em->flush();
		return $tierUsage;
	}

	public function delete(TierUsage $tierUsage): void
	{
		$this->_em->remove($tierUsage);
		$this->_em->flush();
	}

	public function deleteAll(): void
	{
		$this->createQueryBuilder('pt')
			->delete()
			->getQuery()
			->execute();
		$this->_em->flush();
	}

	public function findOne($tierUsage): ?TierUsage
	{
		return $this->createQueryBuilder('u')
			->addSelect(['t', 'ua', 'a', 'ui', 'i', 'us', 'n', 'um', 'm', 'ty', 'tm', 'p1' /* , 'pc', 'p2' */])
			->leftJoin('u.tier', 't')
			->leftJoin('u.usageAbilities', 'ua')
			->leftJoin('ua.ability', 'a')
			->leftJoin('u.usageItems', 'ui')
			->leftJoin('ui.item', 'i')
			->leftJoin('u.usageSpreads', 'us')
			->leftJoin('us.nature', 'n')
			->leftJoin('u.usageMoves', 'um')
			->leftJoin('um.move', 'm')
			->leftJoin('m.type', 'ty')
			->leftJoin('u.teamMates', 'tm')
			->leftJoin('tm.pokemon', 'p1')
			// ->leftJoin('u.pokemonChecks', 'pc')
			// ->leftJoin('pc.pokemon', 'p2')
			->where('u.id = :id')
			->setParameter('id', $tierUsage)
			->getQuery()
			->getOneOrNullResult();
	}

	public function findByPokemon($pokemon)
	{
		return $this->createQueryBuilder('u')
			->addSelect(['t', 'ua'])
			->addSelect(['-t.sortOrder AS HIDDEN inverse_order'])
			->innerJoin('u.tier', 't')
			->leftJoin('u.usageAbilities', 'ua')
			->where('u.pokemon = :pokemon')
			->andWhere('u.percent >= 1')
			->andWhere('t.playable = 1')
			->setParameter('pokemon', $pokemon)
			->addOrderBy('inverse_order', 'DESC')
			->addOrderBy('u.percent', 'DESC')
			->getQuery()
			->getResult();
	}

	public function findOneByFirstRank(Tier $tier): ?TierUsage
	{
		return $this->createQueryBuilder('u')
			->addSelect(['u', 'p'])
			->innerJoin('u.pokemon', 'p')
			->where('u.tier = :tier')
			->andWhere('u.rank = 1')
			->setParameter('tier', $tier)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();
	}

	/**
	 * La gen est inclu dans le tier id
	 * @return array
	 */
	public function findByTier(Tier $tier)
	{
		return $this->createQueryBuilder('u')
			->leftJoin('u.tier', 't')
			->where('u.tier = :tier')
			->andWhere('u.percent >= 1')
			->setParameter('tier', $tier)
			->orderBy('u.percent', 'DESC')
			->getQuery()
			->getResult();
	}
}
