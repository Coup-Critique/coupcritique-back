<?php

namespace App\Repository;

use App\Entity\Nature;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Nature|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nature|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nature[]    findAll()
 * @method Nature[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Nature::class);
    }

    /**
     * @return Nature
     */
    public function insert(Nature $nature)
    {
        $this->_em->persist($nature);
        $this->_em->flush();
        return $nature;
    }

    public function delete(Nature $nature): void
    {
        $this->_em->remove($nature);
        $this->_em->flush();
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('n')
            ->delete()
            ->getQuery()
            ->execute();
        $this->_em->flush();
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('n')
            ->orderBy('IFNULL(n.nom, n.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param $value
     * @return array
     */
    public function search($value)
    {
        return $this->createQueryBuilder('n')
            ->where('n.name LIKE :search OR n.nom LIKE :search')
            ->setParameter('search', "%$value%")
            ->orderBy('IFNULL(n.nom, n.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
