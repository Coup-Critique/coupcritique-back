<?php

namespace App\Repository;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\CircuitTour;
use App\Repository\Abstracts\AbstractArticleRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CircuitTourRepository extends AbstractArticleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CircuitTour::class);
    }

    public function findForCalendar(): array
    {
        $startDate = new \DateTime();
        $startDate->setDate($startDate->format('Y'), $startDate->format('m'), 1);
        $endDate = new \DateTime();
        $endDate->modify('+1 year');
        $endDate->setDate($endDate->format('Y'), $endDate->format('m'), date('t', $endDate->getTimestamp()));
        
        return $this->createQueryBuilder('c')
            ->where('c.endDate >= :startDate')
            ->andWhere('c.endDate <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('c.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOne($id): ?AbstractArticle
    {
        return  $this->createQueryBuilder('a')
            ->addSelect('u')
            ->leftJoin('a.user', 'u')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return AbstractArticle[] Returns an array of Article objects
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('u')
            ->leftJoin('a.user', 'u')
            ->addOrderBy('a.date_creation', $this->order)
            ->setMaxResults($this->maxLength)
            ->getQuery()
            ->getResult();
    }

    public function findWithMax($max): array
    {
        $ids = $this->createQueryBuilder('a')
            ->select('a.id')
            ->addOrderBy('a.date_creation', self::DESC)
            ->setMaxResults($max)
            ->getQuery()
            ->getArrayResult();

        if (empty($ids)) {
            return [];
        }

        $ids = array_map(fn ($res) => $res['id'], $ids);

        return $this->createQueryBuilder('a')
            ->addSelect('u')
            ->leftJoin('a.user', 'u')
            ->andWhere("a.id IN (" . implode(',', $ids) . ")")
            ->addOrderBy('a.date_creation',  self::DESC)
            ->getQuery()->getResult();
    }

    public function findWithQuery(?array $tags = null, ?string $search = null): array
    {
        $query = $this->createQueryBuilder('a')
            ->addSelect('u')
            ->leftJoin('a.user', 'u');

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

        $query->addOrderBy('a.date_creation', $this->order);

        return $query->getQuery()->getResult();
    }
}
