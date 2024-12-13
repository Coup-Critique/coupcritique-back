<?php

namespace App\Repository;

use App\Entity\PasswordTokenRenew;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PasswordTokenRenew|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordTokenRenew|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordTokenRenew[]    findAll()
 * @method PasswordTokenRenew[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordTokenRenewRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, PasswordTokenRenew::class);
	}

	public function createToken(User $user)
	{
		/** @var PasswordTokenRenew|null $refresh_token */
		$refresh_token = $this->findOneByUser($user);

		if (!is_null($refresh_token)) {
			$this->_em->remove($refresh_token);
			$this->_em->flush();
		}
		$refresh_token = new PasswordTokenRenew;
		$refresh_token->createToken($user);
		$this->_em->persist($refresh_token);
		$this->_em->flush();

		return $refresh_token;
	}

	public function delete(PasswordTokenRenew $passwordTokenRenew): void
	{
		$this->_em->remove($passwordTokenRenew);
		$this->_em->flush();
	}
}
