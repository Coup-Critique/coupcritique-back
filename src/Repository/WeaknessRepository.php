<?php

namespace App\Repository;

use App\Entity\Weakness;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Weakness|null find($id, $lockMode = null, $lockVersion = null)
 * @method Weakness|null findOneBy(array $criteria, array $orderBy = null)
 * @method Weakness[]    findAll()
 * @method Weakness[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeaknessRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Weakness::class);
	}

	/**
	 * @return Weakness
	 */
	public function insert(Weakness $weakness)
	{
		$this->_em->persist($weakness);
		$this->_em->flush();
		return $weakness;
	}
}
