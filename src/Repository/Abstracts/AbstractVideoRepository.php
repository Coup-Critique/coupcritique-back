<?php

namespace App\Repository\Abstracts;

use App\Entity\Abstracts\AbstractVideo;
use App\Repository\Traits\MaxLengthTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class AbstractVideoRepository extends ServiceEntityRepository
{
    use MaxLengthTrait;

    public function findAll(): array
    {
        return $this->createQueryBuilder('v')
            ->addSelect(['tag'])
            ->leftJoin("v.tags", 'tag')
            ->addOrderBy('v.date_creation', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->setMaxResults($this->maxLength)
            ->getQuery()
            ->getResult();
    }

    public function findWithQuery(?array $tags = null, $author = null)
    {
        $query = $this->createQueryBuilder('v')
            ->addSelect(['tag']);

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

    public function findWithMax($max)
    {
        $ids = $this->createQueryBuilder('v')
            ->select('v.id')
            ->addOrderBy('v.date_creation', 'DESC')
            ->addOrderBy('v.id', 'DESC')
            ->setMaxResults($max)
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

    public function insert(AbstractVideo $video): AbstractVideo
    {
        $video->setDateCreation(new \DateTime());
        $this->_em->persist($video);
        $this->_em->flush();
        return $video;
    }

    public function delete(AbstractVideo $video): void
    {
        $this->_em->remove($video);
        $this->_em->flush();
    }

    public function update(AbstractVideo $video): AbstractVideo
    {
        $this->_em->persist($video);
        $this->_em->flush();
        return $video;
    }

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
