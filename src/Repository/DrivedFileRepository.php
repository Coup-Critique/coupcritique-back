<?php

namespace App\Repository;

use App\Entity\DrivedFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DrivedFile>
 *
 * @method DrivedFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method DrivedFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method DrivedFile[]    findAll()
 * @method DrivedFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DrivedFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DrivedFile::class);
    }

    /**
     * @param string[] $files
     * @return DrivedFile[]
     */
    public function save(array $filenames, bool $flush = true): array
    {
        $files = [];
        foreach ($filenames as $filename) {
            $file = new DrivedFile();
            $file->setFilename($filename);
            $this->_em->persist($file);
            $files[] = $file;
        }
        if ($flush) {
            $this->_em->flush();
        }
        return $files;
    }

    public function remove(DrivedFile $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }
}
