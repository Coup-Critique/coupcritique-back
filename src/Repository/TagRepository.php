<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Repository\Abstracts\AbstractTagRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends AbstractTagRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Tag::class);
	}
}
