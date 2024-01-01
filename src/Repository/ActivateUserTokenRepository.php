<?php

namespace App\Repository;

use App\Entity\ActivateUserToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ActivateUserToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivateUserToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivateUserToken[]    findAll()
 * @method ActivateUserToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivateUserTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivateUserToken::class);
    }

    public function createToken(User $user)
    {
        /** @var ActivateUserToken|null $activateUserToken */
        $activateUserToken = $this->findOneByUser($user);

        if (is_null($activateUserToken)) {
            $activateUserToken = new ActivateUserToken;
            $activateUserToken->createToken($user);
            $this->_em->persist($activateUserToken);
        } else {
            $activateUserToken->createToken($user);
        }

        $this->_em->flush();
        
        return $activateUserToken;
    }

    public function delete(ActivateUserToken $activateUserToken): void
    {
        $this->_em->remove($activateUserToken);
        $this->_em->flush();
    }
}
