<?php

namespace App\Repository;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Tier;
use App\Entity\TierUsage;
use App\Entity\Type;
use App\Entity\UsageMove;
use App\Entity\User;
use App\Repository\Traits\GenPropertyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Move|null find($id, $lockMode = null, $lockVersion = null)
 * @method Move|null findOneBy(array $criteria, array $orderBy = null)
 * @method Move[]    findAll()
 * @method Move[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoveRepository extends ServiceEntityRepository
{

    use GenPropertyRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Move::class);
    }

    public function insert(Move $move)
    {
        $this->_em->persist($move);
        $this->_em->flush();
        return $move;
    }

    public function update(Move $move, User $user)
    {
        $move->setUpdateDate(new \DateTime());
        $move->setUser($user);
        $move->setSaveDescr($move->getDescription());
        $this->_em->flush();
        return $move;
    }

    public function delete(Move $move)
    {
        $this->_em->remove($move);
        $this->_em->flush();
    }

    public function deleteAll()
    {
        $this->createQueryBuilder('m')
            ->delete()
            ->getQuery()
            ->execute();
        $this->_em->flush();
    }

    /**
     * @param $value Move's name
     * @param int|null $gen desired generation
     * @return array
     */
    public function search($value, $gen)
    {
        $query = $this->createQueryBuilder('m')
            ->where("REPLACE(m.nom,'-',' ') LIKE :search OR REPLACE(m.name,'-',' ') LIKE :search ")
            ->andWhere('m.gen = :gen')
            ->setParameter('search', "%$value%")
            ->setParameter('gen', $gen);

        return $query->orderBy('IFNULL(m.nom, m.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Pokemon $pokemon
     * @return array
     *
     * Custom because PokemonMove is an entity
     * gen include in id
     */
    public function findByPokemon(Pokemon $pokemon)
    {
        $query = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->leftJoin('m.type', 't')
            ->innerJoin(PokemonMove::class, 'pm', Join::WITH, 'pm.move = m.id')
            ->where('pm.pokemon = :pokemon')
            ->setParameter('pokemon', $pokemon);

        return $query->orderBy('IFNULL(m.nom, m.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByType(Type $type)
    {
        $query = $this->createQueryBuilder('m')
            ->addSelect('t')
            ->leftJoin('m.type', 't')
            ->where('m.type = :type')
            ->setParameter('type', $type);

        return $query->orderBy('IFNULL(m.nom, m.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPokemonUsage(Pokemon $pokemon)
    {
        $gen = $pokemon->getGen();
        /** @var TierRepository */
        $tierRepo = $this->getEntityManager()->getRepository(Tier::class);
        $vgc = $tierRepo->findOneByNameAndGen('VGC', $gen);
        $bss = $tierRepo->findOneByNameAndGen('BSS', $gen);

        $query = $this->createQueryBuilder('m')
            ->select(['m', 'um', 'tu', 'mt', 'pm', 'p', 't', 'tp'])
            ->innerJoin(
                PokemonMove::class,
                'pm',
                Join::WITH,
                'pm.move = m.id AND pm.pokemon = :pokemon'
            )
            ->innerJoin('pm.pokemon', 'p')
            ->leftJoin('p.tier', 't')
            ->leftJoin('t.parent', 'tp')
            ->leftJoin('m.type', 'mt')
            ->leftJoin(
                TierUsage::class,
                'tu',
                Join::WITH,
                'tu.pokemon = p.id AND tu.tier = IFNULL(tp.id, t.id)'
            )
            ->leftJoin(
                UsageMove::class,
                'um',
                Join::WITH,
                'um.tierUsage = tu.id AND um.move = m.id'
            )
            ->where('pm.pokemon = :pokemon')
            ->andWhere('pm.id IS NOT NULL')
            ->setParameter('pokemon', $pokemon);

        if (!empty($vgc)) {
            $query->addSelect(['umVgc', 'tuVgc'])
                ->leftJoin(
                    TierUsage::class,
                    'tuVgc',
                    Join::WITH,
                    'tuVgc.pokemon = p.id AND tuVgc.tier = :vgc'
                )
                ->leftJoin(
                    UsageMove::class,
                    'umVgc',
                    Join::WITH,
                    'umVgc.tierUsage = tuVgc.id AND umVgc.move = m.id'
                )
                ->setParameter('vgc', $vgc);
        }

        if (!empty($bss)) {
            $query->addSelect(['umBss', 'tuBss'])
                ->leftJoin(
                    TierUsage::class,
                    'tuBss',
                    Join::WITH,
                    'tuBss.pokemon = p.id AND tuBss.tier = :bss'
                )
                ->leftJoin(
                    UsageMove::class,
                    'umBss',
                    Join::WITH,
                    'umBss.tierUsage = tuBss.id AND umBss.move = m.id'
                )
                ->setParameter('bss', $bss);
        }

        $query->addOrderBy('um.percent', 'DESC');
        if (!empty($vgc)) $query->addOrderBy('umVgc.percent', 'DESC');
        if (!empty($bss)) $query->addOrderBy('umBss.percent', 'DESC');
        $query->addOrderBy('m.nom', 'ASC');

        return $query->getQuery()->getResult();
    }
}
