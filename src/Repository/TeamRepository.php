<?php

namespace App\Repository;

use App\Entity\Pokemon;
use App\Entity\Team;
use App\Entity\User;
use App\Repository\Traits\PaginatorTrait;
use App\Repository\Traits\OrderTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
	use PaginatorTrait;
	use OrderTrait;

	private bool $hasTeamId = false;

	public function __construct(
		ManagerRegistry $registry,
		private readonly PokemonInstanceRepository $pokemonInstanceRepo
	) {
		parent::__construct($registry, Team::class);
	}

	public function setHasTeamId(bool $hasTeamId): self
	{
		$this->hasTeamId = $hasTeamId;
		return $this;
	}

	public function insert(Team $team, User $user)
	{
		$team->setDateCreation(new \DateTime());
		$team->setUser($user);
		$team->setBanned(false);
		$this->_em->persist($team);
		$this->_em->flush();
		return $team;
	}

	public function update(Team $team)
	{
		$team->setUpdateDate(new \DateTime());
		$this->_em->persist($team);
		$this->_em->flush();
		return $team;
	}

	public function delete(Team $team): void
	{
		$this->_em->remove($team);
		$this->_em->flush();
	}

	public function deleteAll(): void
	{
		$this->createQueryBuilder('t')
			->delete()
			->getQuery()
			->execute();
		$this->_em->flush();
	}

	protected function baseQuery(): QueryBuilder
	{
		return $this->createQueryBuilder('t')
			->addSelect([
				'u',
				'enj',
				'ti',
				'tag',
				'r',
				'pi1',
				'pi2',
				'pi3',
				'pi4',
				'pi5',
				'pi6'
			])
			->leftJoin('t.user', 'u')
			->leftJoin('t.enjoyers', 'enj')
			->leftJoin('t.tier', 'ti')
			->leftJoin('t.tags', 'tag')
			->leftJoin('t.replays', 'r')
			->leftJoin('t.pkm_inst_1', 'pi1')
			->leftJoin('t.pkm_inst_2', 'pi2')
			->leftJoin('t.pkm_inst_3', 'pi3')
			->leftJoin('t.pkm_inst_4', 'pi4')
			->leftJoin('t.pkm_inst_5', 'pi5')
			->leftJoin('t.pkm_inst_6', 'pi6');
	}

	public function findOne($id): ?Team
	{
		$result = $this->baseQuery()
			->andWhere('t.id = :id')
			->setParameter('id', $id)
			->addOrderBy('tag.sortOrder', 'ASC')
			->getQuery()
			->getOneOrNullResult();

		if (!is_null($result)) {
			foreach ($result->getPokemonInstances() as $instance) {
				$this->pokemonInstanceRepo->findOne($instance->getId());
			}
		}

		return $result;
	}

	public function findAllCommand()
	{
		return $this->baseQuery()
			->where('t.banned IS NULL OR t.banned = 0')
			->getQuery()
			->getResult();
	}

	protected function baseListQuery(): QueryBuilder
	{
		$query = $this->createQueryBuilder('t')
			->addSelect([
				'u',
				'enj',
				'ti',
				'tag',
				'pi1',
				'pi2',
				'pi3',
				'pi4',
				'pi5',
				'pi6',
				'pk1',
				'pk2',
				'pk3',
				'pk4',
				'pk5',
				'pk6'
			])
			->leftJoin('t.user', 'u')
			->leftJoin('t.enjoyers', 'enj')
			->leftJoin('t.tier', 'ti')
			->leftJoin('t.tags', 'tag')
			->leftJoin('t.pkm_inst_1', 'pi1')
			->leftJoin('t.pkm_inst_2', 'pi2')
			->leftJoin('t.pkm_inst_3', 'pi3')
			->leftJoin('t.pkm_inst_4', 'pi4')
			->leftJoin('t.pkm_inst_5', 'pi5')
			->leftJoin('t.pkm_inst_6', 'pi6')
			->leftJoin('pi1.pokemon', 'pk1')
			->leftJoin('pi2.pokemon', 'pk2')
			->leftJoin('pi3.pokemon', 'pk3')
			->leftJoin('pi4.pokemon', 'pk4')
			->leftJoin('pi5.pokemon', 'pk5')
			->leftJoin('pi6.pokemon', 'pk6');

		return $query;
	}

	public function findWithQuery(?array $criteria = null, ?string $search = null)
	{
		$query = $this->baseListQuery()
			->where('t.banned IS NULL OR t.banned = 0')
			// shadow ban old official meta teams
			->andWhere('ti.playable = 1');

		if (!is_null($criteria)) {
			foreach ($criteria as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $index => $subval) {
						$keyIndex = $key . $index;
						$query->innerJoin("t.$key", $keyIndex);
						$query->andWhere("$keyIndex.id = $subval");
					}
				} else {
					$query->andWhere($value == "0" ? "t.$key = :$key OR t.$key IS NULL" : "t.$key = :$key")
						->setParameter("$key", $value);
				}
			}
		}

		if ($this->hasTeamId) {
			$query->andWhere('t.team_id IS NOT NULL');
		}

		if (!is_null($search)) {
			$searches = explode(',', $search, 8);
			foreach ($searches as $i => $_search) {
				$_search = trim($_search);
				$this->setSearchToQuery($query, $_search, $i);
			}
		}

		$selects = null;
		if (empty($this->order)) {
			$selects = [
				"CASE WHEN (
					DATE_FORMAT(t.date_creation,'%Y') = :currentYear
					AND DATE_FORMAT(t.date_creation,'%m') <= :currentMonth 
					AND DATE_FORMAT(t.date_creation,'%m') > :lastMonthLimit 
				) THEN 0 ELSE 1 END AS HIDDEN recentOrder",
				"CASE WHEN t.certified = 1 THEN 1 ELSE 0 END AS HIDDEN certifOrder"
			];
			$currentDate = new \DateTime();
			$currentMonth = $currentDate->format('m');
			$currentYear = $currentDate->format('Y');
			$params = [
				"currentMonth" => $currentMonth,
				"lastMonthLimit" => $currentMonth - 6,
				"currentYear" => $currentYear
			];
			$this->addPaginatorSelect($selects, $query, $params);

			$query->orderBy('recentOrder', 'ASC')
				->addOrderBy('certifOrder', 'DESC')
				->addOrderBy('t.date_creation', 'DESC')
				->addOrderBy('tag.sortOrder', 'ASC')
				->addOrderBy('t.id', 'DESC');

			foreach ($params as $key => $value) {
				$query->setParameter($key, $value);
			}
		} else {
			$this->setOrderInQuery($query);
		}

		return $this->paginate('t', $query, $this->getPage());
	}

	public function getLastTopWeek(): ?Team
	{
		$query = $this->baseQuery()
			->andWhere('t.banned IS NULL OR t.banned = 0')
			->orderBy('t.top_week', 'DESC')
			->addOrderBy('tag.sortOrder', 'ASC');

		$this->setMaxResults(1);
		$result = $this->paginate('t', $query, 1);

		if (empty($result)) return null;
		$team = $result[0];
		foreach ($team->getPokemonInstances() as $instance) {
			$this->pokemonInstanceRepo->findOne($instance->getId());
		}
		return $team;
	}

	/**
	 * Change top_week attribute in team
	 */
	public function setTopWeek($team): ?Team
	{
		if (!$team instanceof Team) {
			// if id and not instance is given
			$team = $this->find($team);
		}
		if ($team) {
			$team->setTopWeek(new \DateTime());
			$this->update($team);
		}
		return $team;
	}

	public function findByState($state = null, ?array $criteria = null, ?string $search = null)
	{
		$query = $this->baseListQuery();

		if (!is_null($criteria)) {
			foreach ($criteria as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $index => $subval) {
						$keyIndex = $key . $index;
						$query->innerJoin("t.$key", $keyIndex);
						$query->andWhere("$keyIndex.id = $subval");
					}
				} else {
					$query->andWhere($value == "0" ? "t.$key = :$key OR t.$key IS NULL" : "t.$key = :$key")
						->setParameter("$key", $value);
				}
			}
		}

		if (!is_null($search)) {
			$searches = explode(',', $search, 8);
			foreach ($searches as $i => $_search) {
				$_search = trim($_search);
				$this->setSearchToQuery($query, $_search, $i);
			}
		}

		if ($state === 'banned') {
			$query->andWhere('t.banned = 1');
		} else if (is_bool($state)) {
			$query->andWhere('t.certified = :certified')
				->setParameter('certified', $state)
				->andWhere('t.banned IS NULL OR t.banned = 0');
		} else {
			$query->andWhere('t.certified IS NULL')
				->andWhere('t.banned IS NULL OR t.banned = 0');
		}

		$this->setOrderInQuery($query);
		return $this->paginate('t', $query, $this->getPage());
	}

	/**
	 * @return array
	 */
	public function findCertifiedTeamsByPokemon(Pokemon $pokemon): array
	{
		$query = $this->baseListQuery()
			->where('t.certified = 1')
			->andWhere('t.banned IS NULL OR t.banned = 0')
			->andWhere('ti.playable = 1')
			->andWhere("(
				pi1.pokemon = :pokemon
				OR pi2.pokemon = :pokemon
				OR pi3.pokemon = :pokemon
				OR pi4.pokemon = :pokemon
				OR pi5.pokemon = :pokemon
				OR pi6.pokemon = :pokemon
			)")
			->setParameter('pokemon', $pokemon);

		$this->setOrderInQuery($query);
		return $this->paginate('t', $query, $this->getPage());
	}

	public function findByPokemons(array $pokemons): array
	{
		$query = $this->baseListQuery()
			->where('t.banned IS NULL OR t.banned = 0')
			->andWhere('ti.playable = 1');

		foreach ($pokemons as $i => $pokemon) {
			$query
				->andWhere("(
					pi1.pokemon = ?$i
					OR pi2.pokemon = ?$i
					OR pi3.pokemon = ?$i
					OR pi4.pokemon = ?$i
					OR pi5.pokemon = ?$i
					OR pi6.pokemon = ?$i
				)")
				->setParameter($i, $pokemon);
		}

		if (empty($this->order)) {
			$query->addOrderBy('t.certified', 'DESC')
				->addOrderBy('t.date_creation', 'DESC')
				->addOrderBy('tag.sortOrder', 'ASC')
				->addOrderBy('t.id', 'DESC');
		} else {
			$this->setOrderInQuery($query);
		}
		return $this->paginate('t', $query, $this->getPage());
	}

	public function findbyUser(User $user, ?bool $banned = false)
	{
		$query = $this->baseListQuery()
			->where('t.user = :user')
			->setParameter('user', $user);

		if (!$banned) {
			$query->andWhere('t.banned IS NULL OR t.banned = 0 OR t.certified = 1');
		}

		$this->setOrderInQuery($query);
		return $this->paginate('t', $query, $this->getPage());
	}

	public function findbyFavorites(User $user)
	{
		$query = $this->baseListQuery()
			->where('enj.id = :user')
			->andWhere('t.banned IS NULL OR t.banned = 0')
			->setParameter('user', $user);

		$this->setOrderInQuery($query);
		return $this->paginate('t', $query, $this->getPage());
	}

	public function findUntreatedbyUser(User $user)
	{
		return $this->createQueryBuilder('t')
			->addSelect(['u', 'ti'])
			->leftJoin('t.user', 'u')
			->leftJoin('t.tier', 'ti')
			->where('t.user = :user')
			->andWhere('t.certified IS NULL')
			->andWhere('t.banned IS NULL OR t.banned = 0')
			->setParameter('user', $user)
			->getQuery()
			->getResult();
	}

	public function findLastUserTeam(User $user): ?Team
	{
		$query = $this->baseListQuery()
			->where('t.user = :user')
			->andWhere('t.banned IS NULL OR t.banned = 0')
			->setParameter('user', $user)
			->orderBy('t.date_creation', 'DESC')
			->addOrderBy('tag.sortOrder', 'ASC')
			->addOrderBy('t.id', 'DESC');

		$this->setMaxResults(1);
		$result = $this->paginate('t', $query, 1);

		if (empty($result)) return null;
		$team = $result[0];
		foreach ($team->getPokemonInstances() as $instance) {
			$this->pokemonInstanceRepo->findOne($instance->getId());
		}
		return $team;
	}

	private function setSearchToQuery(QueryBuilder $query, string $search, int $i = 0): void
	{
		$query->andWhere(
			"t.name LIKE :search$i"
				. " OR t.team_id LIKE :search$i"
				. " OR u.username LIKE :search$i"
				// . " OR ti.name LIKE :search$i"
				// . " OR ti.shortName LIKE :search$i"
				. " OR tag.name LIKE :search$i"
				. " OR tag.shortName LIKE :search$i"
				. " OR pk1.name LIKE :search$i"
				. " OR pk2.name LIKE :search$i"
				. " OR pk3.name LIKE :search$i"
				. " OR pk4.name LIKE :search$i"
				. " OR pk5.name LIKE :search$i"
				. " OR pk6.name LIKE :search$i"
				. " OR pk1.nom LIKE :search$i"
				. " OR pk2.nom LIKE :search$i"
				. " OR pk3.nom LIKE :search$i"
				. " OR pk4.nom LIKE :search$i"
				. " OR pk5.nom LIKE :search$i"
				. " OR pk6.nom LIKE :search$i"
		)->setParameter("search$i", "%$search%");
	}

	private function setOrderInQuery(QueryBuilder $query): void
	{
		$od = $this->getOrderDirection();

		switch ($this->order) {
			case 'name':
				$query->orderBy('t.name', $od)
					->addOrderBy('t.date_creation', 'DESC')
					->addOrderBy('t.id', 'DESC');
				break;
			case 'certified':
				$this->addPaginatorSelect('CASE WHEN t.certified = 1 THEN 1 ELSE 0 END AS HIDDEN certifOrder', $query);
				$query->orderBy('certifOrder', $od)
					->addOrderBy('t.date_creation', 'DESC')
					->addOrderBy('t.id', 'DESC');
				break;
			case 'user':
				$query->orderBy('u.username', $od)
					->addOrderBy('t.date_creation', 'DESC')
					->addOrderBy('t.id', 'DESC');
				break;
			case 'tier':
				$this->addPaginatorSelect('-ti.sortOrder AS HIDDEN inverse_order', $query);
				$query->orderBy('inverse_order', $od === 'ASC' ? 'DESC' : 'ASC')
					->addOrderBy('ti.name', 'ASC')
					->addOrderBy('t.date_creation', 'DESC')
					->addOrderBy('t.id', 'DESC');
				break;
			case 'tag':
				$query->orderBy('tag.name', $od)
					->addOrderBy('t.date_creation', 'DESC')
					->addOrderBy('t.id', 'DESC');
				break;
			case 'date_creation':
				$query->orderBy('t.date_creation', $od)
					->addOrderBy('t.id', $od);
				break;
			case 'countEnjoyers':
				$subquery = $this->_em->getRepository(User::class)->createQueryBuilder('subu')
					->select('COUNT(subu.id)')
					->innerJoin('subu.favorites', 'subt')
					->where('subt.id = t.id')
					->getDQL();
				$this->addPaginatorSelect("($subquery) AS HIDDEN countEnjoyers", $query);
				$query->orderBy('countEnjoyers', $od)->addOrderBy('t.id', $od);
				break;
			default:
				$query->orderBy('t.date_creation', 'DESC')
					->addOrderBy('t.id', 'DESC');
				break;
		}
		$query->addOrderBy('tag.sortOrder', 'ASC');
	}
}
