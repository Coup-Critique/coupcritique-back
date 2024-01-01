<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
	public function __construct(
		ManagerRegistry $registry,
		private readonly UserPasswordHasherInterface $passwordEncoder
	) {
		parent::__construct($registry, User::class);
	}

	/**
	 * Used to upgrade (rehash) the user's password automatically over time.
	 */
	public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
	{
		if (!$user instanceof User) {
			throw new UnsupportedUserException(
				sprintf('Instances of "%s" are not supported.', $user::class)
			);
		}

		$user->setPassword($newHashedPassword);
		$this->_em->persist($user);
		$this->_em->flush();
	}

	public function insert(User $user): User
	{
		$user->setPassword($this->passwordEncoder->hashPassword($user, $user->getPassword()));
		$user->setActivated(false);
		$user->setBanned(false);
		$user->setDeleted(false);
		$user->setDateCreation(new \DateTime());
		$this->_em->persist($user);
		$this->_em->flush();
		return $user;
	}

	public function delete(User $user): void
	{
		$user->setDeleted(true);
		$this->_em->persist($user);
		$this->_em->flush();
	}

	/**
	 * @return User
	 */
	public function update(User $user): User
	{
		$this->_em->persist($user);
		$this->_em->flush();
		return $user;
	}

	public function updatePassword(User $user, string $password): User
	{
		$user->setPassword($this->passwordEncoder->hashPassword($user, $password));
		$this->_em->persist($user);
		$this->_em->flush();
		return $user;
	}

	public function checkPassword(User $user, string $password): bool
	{
		return $this->passwordEncoder->isPasswordValid($user, $password);
	}

	public function findAll(): array
	{
		return $this->createQueryBuilder('u')
			->where("u.deleted = 0 OR u.deleted IS NULL")
			->andWhere("u.banned = 0 OR u.banned IS NULL")
			->orderBy('u.username', 'ASC')
			->getQuery()
			->getResult();
	}

	public function findAllForAdmin(?string $search = null): array
	{
		$query = $this->createQueryBuilder('u')
			->orderBy('u.date_creation', 'DESC');

		if ($search) {
			$query->where('u.username LIKE :search')
				->orWhere('u.discord_name LIKE :search')
				->orWhere('u.showdown_name LIKE :search')
				->setParameter('search', "%$search%");
		}

		return $query->getQuery()->getResult();
	}

	public function search(string $username, int $limit = null): array
	{
		$statement = $this->createQueryBuilder('u')
			->where('u.username LIKE :username')
			->orWhere('u.discord_name LIKE :username')
			->orWhere('u.showdown_name LIKE :username')
			->setParameter('username', "%$username%")
			->orderBy('u.username', 'ASC');

		if (!is_null($limit)) $statement->setMaxResults($limit);

		return $statement->getQuery()->getResult();
	}

	public function ipIsBanned(string $ip): int
	{
		return $this->createQueryBuilder('u')
			->select('COUNT(u.id)')
			->where("u.banned = 1")
			->andWhere('u.ips LIKE :ip')
			->setParameter('ip', "%\"$ip\"%")
			->getQuery()
			->getSingleScalarResult();
	}

	public function likeBanned(User $user): int
	{
		return $this->createQueryBuilder('u')
			->select('COUNT(u.id)')
			->where("u.banned = 1")
			->andWhere('u.showdown_name LIKE :showdown')
			->andWhere('u.discord_name LIKE :discord')
			->setParameter('showdown', $user->getShowdownName())
			->setParameter('discord', $user->getDiscordName())
			->getQuery()
			->getSingleScalarResult();
	}
}
