<?php

namespace App\Repository;

use App\Entity\CircuitVideo;
use App\Repository\Abstracts\AbstractVideoRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CircuitVideo|null find($id, $lockMode = null, $lockVersion = null)
 * @method CircuitVideo|null findOneBy(array $criteria, array $orderBy = null)
 * @method CircuitVideo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CircuitVideoRepository extends AbstractVideoRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CircuitVideo::class);
    }

    public function findWithMax($max, $idTour = null)
    {
        $queryIds = $this->createQueryBuilder('v')
            ->select('v.id')
            ->addOrderBy('v.date_creation', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->setMaxResults($max);

        if ($idTour != null) {
            $queryIds->andWhere('v.tour = :idTour')
                ->setParameter('idTour', $idTour);
        }

        $ids = $queryIds
            ->getQuery()
            ->getArrayResult();

        if (empty($ids)) return [];

        $ids = array_map(fn ($res) => $res['id'], $ids);

        return $this->createQueryBuilder('v')
            ->addSelect(['tag'])
            ->leftJoin('v.tags', 'tag')
            ->andWhere("v.id IN (" . implode(',', $ids) . ")")
            ->addOrderBy('v.date_creation', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->getQuery()->getResult();
    }

    public function findByTour($idTour, ?array $tags = null, $author = null)
    {
        $query = $this->createQueryBuilder('v')
            ->addSelect(['tag'])
            ->andWhere('a.tour = :idTour')
            ->setParameter('idTour', $idTour);

        if ($tags != null) {
            $query->innerJoin('v.tags', 'tag')
                ->innerJoin('v.tags', 'filter_tag')
                ->andWhere("filter_tag.id IN (:ids) ")
                ->setParameter("ids", $tags);
        } else {
            $query->leftJoin('v.tags', 'tag');
        }

        if ($author != null) {
            $query->andWhere("v.author = :author")
                ->setParameter("author", $author);
        }

        $query->addOrderBy('v.date_creation', 'DESC')
            ->addOrderBy('v.id', 'DESC');
        return $query
            ->setMaxResults($this->maxLength)
            ->getQuery()->getResult();
    }
}
