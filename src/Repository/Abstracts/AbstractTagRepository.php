<?php

namespace App\Repository\Abstracts;

use App\Entity\Abstracts\AbstractTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<AbstractTag>
 *
 * @method AbstractTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbstractTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbstractTag[]    findAll()
 * @method AbstractTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
abstract class AbstractTagRepository extends ServiceEntityRepository
{
    public function insert(AbstractTag $abstractTag): AbstractTag
    {
        $this->_em->persist($abstractTag);
        $this->_em->flush();
        return $abstractTag;
    }

    public function delete(AbstractTag  $abstractTag): void
    {
        $this->_em->remove($abstractTag);
        $this->_em->flush();
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('t')
            ->delete()
            ->getQuery()
            ->execute();
        $this->_em->flush();
    }
}
