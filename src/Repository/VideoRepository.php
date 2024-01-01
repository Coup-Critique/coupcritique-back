<?php

namespace App\Repository;

use App\Entity\Video;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Video|null find($id, $lockMode = null, $lockVersion = null)
 * @method Video|null findOneBy(array $criteria, array $orderBy = null)
 * @method Video[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VideoRepository extends ServiceEntityRepository
{
    private int $maxLength = 500;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Video::class);
    }

    public function setMaxLength($maxLength): void
    {
        $maxLength = intval($maxLength);
        if ($maxLength > 0 && $maxLength < 500) {
            $this->maxLength = $maxLength;
        }
    }

    public function findAll(): array
    {
        return $this->createQueryBuilder('v')
            ->addSelect(['tag'])
            ->leftJoin("v.tags", 'tag')
            ->addOrderBy('v.date_creation', 'DESC')
            ->setMaxResults($this->maxLength)
            ->getQuery()
            ->getResult();
    }

    public function findWithQuery(?array $tags = null, $author = null)
    {
        $query = $this->createQueryBuilder('v')
            ->addSelect(['tag']);

        if (!is_null($tags)) {
            $query->innerJoin('v.tags', 'tag')
                ->innerJoin('v.tags', 'filter_tag')
                ->andWhere("filter_tag.id IN (:ids) ")
                ->setParameter("ids", $tags);
        } else {
            $query->leftJoin('v.tags', 'tag');
        }

        if (!is_null($author)) {
            $query->andWhere("v.author = :author")
                ->setParameter("author", $author);
        }

        $query->addOrderBy('v.date_creation', 'DESC');
        return $query->setMaxResults($this->maxLength)->getQuery()->getResult();
    }

    public function findWithMax($max)
    {
        $ids = $this->createQueryBuilder('v')
            ->select('v.id')
            ->addOrderBy('v.date_creation', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getArrayResult();

        if(empty($ids))
            return [];

        $ids = array_map(fn ($res) => $res['id'], $ids);

        return $this->createQueryBuilder('v')
            ->addSelect(['tag'])
            ->leftJoin('v.tags', 'tag')
            ->andWhere("v.id IN (" . implode(',', $ids) . ")")
            ->addOrderBy('v.date_creation', 'DESC')
            ->getQuery()->getResult();
    }

    /**
     * @return Video
     */
    public function insert(Video $video)
    {
        $video->setDateCreation(new \DateTime());
        $this->_em->persist($video);
        $this->_em->flush();
        return $video;
    }

    public function delete(Video $video): void
    {
        $this->_em->remove($video);
        $this->_em->flush();
    }

    /**
     * @return Video
     */
    public function update(Video $video): Video
    {
        $this->_em->persist($video);
        $this->_em->flush();
        return $video;
    }

    /**
     * @return array
     */
    public function findAllAuthors(): array
    {
        return $this->createQueryBuilder('v')
            ->select('v.author')
            ->distinct('v.author')
            ->orderBy('v.author')
            ->getQuery()
            ->getResult();
    }
}
