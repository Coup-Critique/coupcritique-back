<?php

namespace App\Repository;

use App\Entity\Guide;
use App\Repository\Abstracts\AbstractArticleRepository;
use Doctrine\Persistence\ManagerRegistry;

class GuideRepository extends AbstractArticleRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guide::class);
        $this->order = self::ASC;
    }
}
