<?php

namespace App\Repository;

use App\Entity\GuideTag;
use App\Repository\Abstracts\AbstractTagRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GuideTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method GuideTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method GuideTag[]    findAll()
 * @method GuideTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuideTagRepository extends AbstractTagRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, GuideTag::class);
	}
}
