<?php

namespace App\Repository;

use App\Entity\Type;
use App\Entity\Weakness;
use App\Repository\Traits\GenPropertyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Type|null find($id, $lockMode = null, $lockVersion = null)
 * @method Type|null findOneBy(array $criteria, array $orderBy = null)
 * @method Type[]    findAll()
 * @method Type[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TypeRepository extends ServiceEntityRepository
{
    use GenPropertyRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Type::class);
    }

    /**
     * @param Type $type
     * @return Type
     */
    public function insert(Type $type)
    {
        $this->_em->persist($type);
        $this->_em->flush();
        return $type;
    }

    /**
     * @param Type $type
     */
    public function delete(Type $type)
    {
        $this->_em->remove($type);
        $this->_em->flush();
    }

    public function deleteAll()
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->getQuery()
            ->execute();
        $this->_em->flush();
    }

    public function getAvailableGens(string $name)
    {
        return $this->createQueryBuilder('e')
            ->select('e.gen AS gen, e.id AS id')
            ->where('e.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();
    }

    public function findAllByGen($gen)
    {
        return $this->createQueryBuilder('t')
            // ->addSelect('w')
            // ->leftJoin('t.weaknesses', 'w')
            ->andWhere('t.gen = :gen')
            ->setParameter('gen', $gen)
            ->orderBy('IFNULL(t.nom, t.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the element for a specified gen
     * @param string $value Element's name 
     * @param string|int $gen
     * @return mixed|null 
     */
    public function findOneByNameAndGen(string $value, $gen)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.name = :value')
            ->andWhere('e.gen = :gen')
            ->setParameter('value', $value)
            ->setParameter('gen', $gen);
        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $value
     * @return array
     */
    public function search($value, $gen)
    {
        return $this->createQueryBuilder('t')
            ->where("REPLACE(t.nom,'-',' ') LIKE :search OR REPLACE(t.name,'-',' ') LIKE :search ")
            ->andWhere('t.gen LIKE :gen')
            ->setParameter('search', "%$value%")
            ->setParameter('gen', $gen)
            ->orderBy('IFNULL(t.nom, t.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
