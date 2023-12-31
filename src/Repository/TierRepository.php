<?php

namespace App\Repository;

use App\Entity\Tier;
use App\Repository\Traits\GenPropertyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;
use RuntimeException;

/**
 * @method Tier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tier[]    findAll()
 * @method Tier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TierRepository extends ServiceEntityRepository
{
    use GenPropertyRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tier::class);
    }

    /**
     * @return Tier
     */
    public function insert(Tier $tier)
    {
        $this->_em->persist($tier);
        $this->_em->flush();
        return $tier;
    }

    public function delete(Tier $tier)
    {
        $this->_em->remove($tier);
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

    /**
     * @override Trait's methode
     * @param string $value 
     * @param string|int $gen 
     * @return Tier|null
     */
    public function findOneByNameAndGen($value, $gen)
    {
        return $this->createQueryBuilder('t')
            ->where('t.name = :name OR t.shortName = :name')
            ->andWhere('t.gen = :gen')
            ->andWhere("t.name <> 'Untiered'")
            ->setParameter('name', $value)
            ->setParameter('gen', $gen)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByTopAndGen($gen, ?array $criteria = null)
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.gen = :gen')
            ->andWhere('t.playable = 1')
            ->andWhere('t.official = 0 OR t.official IS NULL OR t.main = 1')
            ->setParameter('gen', $gen);

        if (!is_null($criteria)) {
            foreach ($criteria as $key => $value) {
                $query->andWhere(
                    $value == "0" ? "t.$key = :$key OR t.$key IS NULL"
                        : "t.$key = :$key"
                )->setParameter("$key", $value);
            }
        }

        return $query->andWhere('t.sortOrder IS NOT NULL')
            ->addOrderBy('t.sortOrder', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $value
     * @return array
     */
    public function search($value, $gen)
    {
        $query = $this->createQueryBuilder('t')
            ->where("REPLACE(t.shortName,'-',' ') LIKE :search OR REPLACE(t.name,'-',' ') LIKE :search ")
            ->andWhere('t.gen = :gen')
            ->andWhere("t.name <> 'Untiered'")
            ->setParameter('search', "%$value%")
            ->setParameter('gen', $gen);

        return $query->orderBy('IFNULL(t.name, t.shortName)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findList($gen = null)
    {
        $query = $this->createQueryBuilder('t')
            // use -sortOrder to ORDER NULL sortOrder AS LAST, NOT AS FIRST LIKE ASC
            ->addSelect(['-t.sortOrder AS HIDDEN inverse_order'])
            ->where('t.playable = 1')
            ->andWhere('t.official <> 1 OR t.main = 1')
            /* ->where("t.name <> 'Untiered'") */;

        if (!is_null($gen)) {
            $query->andWhere('t.gen = :gen')
                ->setParameter('gen', $gen);
        }

        return $query->addOrderBy('inverse_order', 'DESC')
            ->addOrderBy('IFNULL(t.name, t.shortName)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPlayableAccrossGens()
    {
        return $this->createQueryBuilder('t')
            // use -sortOrder to ORDER NULL sortOrder AS LAST, NOT AS FIRST LIKE ASC
            ->addSelect(['-t.sortOrder AS HIDDEN inverse_order'])
            ->where('t.playable = 1')
            ->addOrderBy('inverse_order', 'DESC')
            ->addOrderBy('IFNULL(t.name, t.shortName)', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
