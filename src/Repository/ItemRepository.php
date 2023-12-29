<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\User;
use App\Repository\Traits\GenPropertyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    use GenPropertyRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function insert(Item $item)
    {
        $this->_em->persist($item);
        $this->_em->flush();
        return $item;
    }
    
    public function update(Item $item, User $user)
    {
        $item->setUpdateDate(new \DateTime());
        $item->setUser($user);
        $item->setSaveDescr($item->getDescription());
        $this->_em->flush();
        return $item;
    }

    public function delete(Item $item)
    {
        $this->_em->remove($item);
        $this->_em->flush();
    }

    public function deleteAll()
    {
        $this->createQueryBuilder('i')
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
        $query = $this->createQueryBuilder('i')
            ->where("REPLACE(i.nom,'-',' ') LIKE :search OR REPLACE(i.name,'-',' ') LIKE :search ")
            ->andWhere('i.gen = :gen')
            ->setParameter('search', "%$value%")
            ->setParameter('gen',$gen);
            
        return $query->orderBy('IFNULL(i.nom, i.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
