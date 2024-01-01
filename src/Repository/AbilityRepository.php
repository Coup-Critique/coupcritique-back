<?php

namespace App\Repository;

use App\Entity\Ability;
use App\Entity\User;
use App\Repository\Traits\GenPropertyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ability|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ability|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ability[]    findAll()
 * @method Ability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbilityRepository extends ServiceEntityRepository
{
    use GenPropertyRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ability::class);
    }

    public function insert(Ability $ability)
    {
        $this->_em->persist($ability);
        $this->_em->flush();
        return $ability;
    }
    
    public function update(Ability $ability, User $user)
    {
        $ability->setUpdateDate(new \DateTime());
        $ability->setUser($user);
        $ability->setSaveDescr($ability->getDescription());
        $this->_em->flush();
        return $ability;
    }
    
    public function delete(Ability $ability): void
    {
        $this->_em->remove($ability);
        $this->_em->flush();
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('a')
            ->delete()
            ->getQuery()
            ->execute();
        $this->_em->flush();
    }

    /**
     * @param $value
     * @param int|null $gen desired generation
     * @return array
     */
    public function search($value, $gen)
    {
        $query = $this->createQueryBuilder('a')
            ->where("REPLACE(a.nom,'-',' ') LIKE :search OR REPLACE(a.name,'-',' ') LIKE :search ")
            ->andWhere('a.gen = :gen')
            ->setParameter('search', "%$value%")
            ->setParameter('gen', $gen);

        return $query->orderBy('IFNULL(a.nom, a.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
