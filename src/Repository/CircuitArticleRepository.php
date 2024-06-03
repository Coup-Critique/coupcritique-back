<?php

namespace App\Repository;

use App\Entity\CircuitArticle;
use App\Repository\Abstracts\AbstractArticleRepository;
use Doctrine\Persistence\ManagerRegistry;

class CircuitArticleRepository extends AbstractArticleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CircuitArticle::class);
    }

    public function findWithMax($max, $idTour = null): array
    {
        $queryIds = $this->createQueryBuilder('a')
            ->select('a.id')
            ->addOrderBy('a.date_creation', self::DESC)
            ->addOrderBy('a.id', self::DESC)
            ->setMaxResults($max);

        if ($idTour != null) {
            $queryIds->andWhere('a.tour = :idTour')
                ->setParameter('idTour', $idTour);
        }

        $ids = $queryIds
            ->getQuery()
            ->getArrayResult();

        if (empty($ids)) {
            return [];
        }

        $ids = array_map(fn ($res) => $res['id'], $ids);

        return $this->createQueryBuilder('a')
            ->addSelect(['tag', 'u'])
            ->leftJoin('a.user', 'u')
            ->leftJoin('a.tags', 'tag')
            ->andWhere("a.id IN (" . implode(',', $ids) . ")")
            ->addOrderBy('a.date_creation',  self::DESC)
            ->addOrderBy('a.id', self::DESC)
            ->getQuery()->getResult();
    }

    public function findByTour($idTour, ?array $tags = null, ?string $search = null): array
    {
        $query = $this->createQueryBuilder('a')
            ->addSelect(['tag', 'u'])
            ->leftJoin('a.user', 'u')
            ->andWhere('a.tour = :idTour')
            ->setParameter('idTour', $idTour);

        if ($tags != null) {
            $query
                ->innerJoin('a.tags', 'tag')
                ->innerJoin('a.tags', 'filter_tag')
                ->andWhere("filter_tag.id in (:ids) ")
                ->setParameter("ids", $tags);
        } else {
            $query->leftJoin('a.tags', 'tag');
        }

        if ($search != null) {
            $searches = explode(',', $search, 8);
            foreach ($searches as $i => $_search) {
                $_search = trim($_search);
                $query->andWhere(
                    "a.title LIKE :search$i"
                        . " OR a.shortDescription LIKE :search$i"
                        . " OR u.username LIKE :search$i"
                )->setParameter("search$i", "%$_search%");
            }
        }

        $query->addOrderBy('a.date_creation', $this->order)
            ->addOrderBy('a.id', $this->order);

        return $query->getQuery()->getResult();
    }
}
