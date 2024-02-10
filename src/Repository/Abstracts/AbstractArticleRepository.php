<?php

namespace App\Repository\Abstracts;

use App\Entity\Abstracts\AbstractArticle;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method AbstractArticle|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractArticle|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractArticle[]    findAll()
 * @method AbstractArticle[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractArticleRepository extends ServiceEntityRepository
{
    final public const ASC = 'ASC';
    final public const DESC = 'DESC';

    protected int $maxLength = 500;
    protected string $order = self::DESC;

    public function setMaxLength($maxLength): void
    {
        $maxLength = intval($maxLength);
        if ($maxLength > 0 && $maxLength < 500) {
            $this->maxLength = $maxLength;
        }
    }

    public function insert(AbstractArticle $article, User $user): AbstractArticle
    {
        $article->setDateCreation(new \DateTime());
        $article->setUser($user);
        $this->_em->persist($article);
        $this->_em->flush();
        return $article;
    }

    public function delete(AbstractArticle $article): void
    {
        $this->_em->remove($article);
        $this->_em->flush();
    }

    /**
     * @return AbstractArticle[] Returns an array of Article objects
     */
    public function search(string $keywords, int $limit = null): array
    {
        $keywords = trim($keywords);
        $statement = $this->createQueryBuilder('a')
            ->where('a.title LIKE :keyword');

        $statement->setParameter('keyword', "%$keywords%")
            ->orderBy('a.date_creation', $this->order);

        if ($limit != null) $statement->setMaxResults($limit);

        return $statement->getQuery()->getResult();
    }

    public function findOne($id): ?AbstractArticle
    {
        return  $this->createQueryBuilder('a')
            ->addSelect(['tag', 'u'])
            ->leftJoin("a.tags", 'tag')
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
            ->addSelect(['tag', 'u'])
            ->leftJoin("a.tags", 'tag')
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
            ->addSelect(['tag', 'u'])
            ->leftJoin('a.user', 'u')
            ->leftJoin('a.tags', 'tag')
            ->andWhere("a.id IN (" . implode(',', $ids) . ")")
            ->addOrderBy('a.date_creation',  self::DESC)
            ->getQuery()->getResult();
    }

    public function findWithQuery(?array $tags = null, ?string $search = null): array
    {
        $query = $this->createQueryBuilder('a')
            ->addSelect(['tag', 'u'])
            ->leftJoin('a.user', 'u');

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

        $query->addOrderBy('a.date_creation', $this->order);

        return $query->getQuery()->getResult();
    }
}
