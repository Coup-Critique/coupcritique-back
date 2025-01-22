<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 *
 * @method Notification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notification[]    findAll()
 * @method Notification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function insert(Notification $notification, bool $flush = true): void
    {
        $notification->setDate(new \DateTime());
        $this->_em->persist($notification);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Notification $notification, bool $flush = true): void
    {
        $this->_em->remove($notification);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findOneByUser($id, User $user): ?Notification
    {
        return $this->createQueryBuilder('n')
            ->addSelect('nn')
            ->leftJoin('n.notifier', 'nn')
            ->where('n.id = :id')
            ->andWhere('n.user = :user')
            ->andWhere('n.viewed = 0')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUser(User $user, bool $viewed = false): ?array
    {
        return $this->createQueryBuilder('n')
            ->addSelect('nn')
            ->leftJoin('n.notifier', 'nn')
            ->where('n.user = :user')
            ->andWhere('n.viewed = ' . ($viewed ? 1 : 0))
            ->setParameter('user', $user)
            ->orderBy('n.date', 'DESC')
            ->setMaxResults(300)
            ->getQuery()
            ->getResult();
    }

    public function countByUser(User $user): ?int
    {
        $result = $this->createQueryBuilder('n')
            ->select("COUNT(n.id) AS counter")
            ->where('n.user = :user')
            ->andWhere('n.viewed = 0')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();

        return $result ? $result['counter'] : 0;
    }

    public function findByEntity(User $user, string $entityName, $entityId): ?array
    {
        return $this->createQueryBuilder('n')
            ->addSelect('nn')
            ->leftJoin('n.notifier', 'nn')
            ->where('n.user = :user')
            ->andWhere('n.entityName = :entityName')
            ->andWhere('n.entityId = :entityId')
            ->andWhere('n.viewed = 0')
            ->setParameter('user', $user)
            ->setParameter('entityName', $entityName)
            ->setParameter('entityId', $entityId)
            ->getQuery()
            ->getResult();
    }

    public function autoRemoveNotifications(): int
    {
        $notifications = $this->createQueryBuilder('n')
            ->leftJoin(Team::class, 't', 'WITH', 'n.entityId = t.id')
            ->andWhere("n.entityName = 'team'")
            ->andWhere('t.id IS NULL')
            ->getQuery()
            ->getResult();

        $count = count($notifications);

        foreach ($notifications as $notification) {
            $this->_em->remove($notification);
        }

        $this->_em->flush();

        return $count;
    }
}
