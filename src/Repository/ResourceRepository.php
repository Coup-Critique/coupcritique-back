<?php

namespace App\Repository;

use App\Entity\Resource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Resource|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resource|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resource[]    findAll()
 * @method Resource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resource::class);
    }

    /**
     * @param Resource $resource
     * @return Resource
     */
    public function insert(Resource $resource)
    {
        $this->_em->persist($resource);
        $this->_em->flush();
        return $resource;
    }

    /**
     * @param Resource $resource
     */
    public function delete(Resource $resource)
    {
        $guide = $resource->getGuide();
        if ($guide) $guide->setResource(null);
        $this->_em->remove($resource);
        $this->_em->flush();
    }

    /**
     * @param Resource $resource
     * @return Resource
     */
    public function update(Resource $resource): Resource
    {
        $this->_em->persist($resource);
        $this->_em->flush();
        return $resource;
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('r')
            ->addSelect('t')
            ->addSelect(['-t.sortOrder AS HIDDEN inverse_order'])
            ->leftJoin('r.tier', 't')
            ->addOrderBy('r.gen', 'DESC')
            ->addOrderBy('r.category', 'ASC')
            ->addOrderBy('inverse_order', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
