<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Interfaces\CommentParentInterface;
use App\Entity\User;
use App\Entity\Vote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function insert(Comment $comment, bool $flush = true): Comment
    {
        $comment->setDateCreation(new \DateTime());
        $this->_em->persist($comment);
        if ($flush) $this->_em->flush();
        return $comment;
    }

    public function update(Comment $comment): Comment
    {
        $this->_em->persist($comment);
        $this->_em->flush();
        return $comment;
    }

    public function delete(Comment $comment, bool $flush = true): Comment
    {
        $comment->setDeleted(new \DateTime());
        if ($flush) $this->_em->flush();
        return $comment;
    }

    public function definitivDelete(Comment $comment): null
    {
        $this->_em->remove($comment);
        $this->_em->flush();
        return null;
    }

    public function findByParent(
        CommentParentInterface $parent,
        string $colName,
        ?User $user
    ) {
        $approval = $this
            ->getEntityManager()
            ->getRepository(Vote::class)
            ->createQueryBuilder('dql_v')
            ->select('SUM(CASE WHEN(dql_v.positiv = 1) THEN 1 ELSE -1 END)')
            ->where('dql_v.comment = ct.id')
            ->getDQL();

        $query = $this->createQueryBuilder('ct')
            ->addSelect(['u', 'r'])
            ->addSelect("($approval) AS HIDDEN _approval")
            ->addSelect('(CASE WHEN ct.approved_by_author = 1 THEN 1 ELSE 0 END) AS HIDDEN _aba')
            ->leftJoin('ct.user', 'u')
            ->leftJoin('ct.replies', 'r')
            ->where("ct.$colName = :$colName")
            ->setParameter($colName, $parent);

        if (!is_null($user)) {
            $userReplies = $this->createQueryBuilder('reply')
                ->select('ur')
                ->innerJoin('reply.user', 'ur')
                ->where('reply.originalOne = ct.id')
                ->getDQL();

            $query->addSelect("(
                    CASE WHEN ct.user = :user THEN 1
                    WHEN :user IN ($userReplies) THEN 1 
                    ELSE 0 END
                ) AS HIDDEN _vu")
                ->setParameter('user', $user)
                ->addOrderBy('_vu', 'DESC');
        }

        return $query->addOrderBy('_aba', 'DESC')
            ->addOrderBy('_approval', 'DESC')
            ->addOrderBy('ct.date_creation', 'DESC')
            ->addOrderBy('ct.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByUser(User $user)
    {
        $result = $this->createQueryBuilder('ct')
            ->select('COUNT(ct.id) AS counter')
            ->where('ct.user = :user')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->getResult();

        return $result[0] ? $result[0]['counter'] : 0;
    }
}
