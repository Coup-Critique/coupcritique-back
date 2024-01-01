<?php

namespace App\Repository;

use App\Entity\Ability;
use App\Entity\Item;
use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonMove;
use App\Entity\Type;
use App\Entity\Tier;
use App\Entity\TierUsage;
use App\Entity\UsageAbility;
use App\Entity\UsageItem;
use App\Entity\UsageMove;
use App\Entity\User;
use App\Repository\Traits\GenPropertyRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Pokemon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pokemon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pokemon[]    findAll()
 * @method Pokemon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PokemonRepository extends ServiceEntityRepository
{
    use GenPropertyRepository;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pokemon::class);
    }

    public function insert(Pokemon $pokemon)
    {
        $this->_em->persist($pokemon);
        $this->_em->flush();
        return $pokemon;
    }

    public function update(Pokemon $pokemon, User $user)
    {
        $pokemon->setUpdateDate(new \DateTime());
        $pokemon->setUser($user);
        $this->_em->flush();
        return $pokemon;
    }

    public function delete(Pokemon $pokemon): void
    {
        $this->_em->remove($pokemon);
        $this->_em->flush();
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('p')
            ->delete()
            ->getQuery()
            ->execute();
        $this->_em->flush();
    }

    protected function baseCompleteQuery(): QueryBuilder
    {
        $query = $this->createQueryBuilder('p')
            ->addSelect([
                't', 'a1', 'a2', 'ah',
                't1', 't2', 't1w', 't2w',
                'f', 'bf', 'e', 'pe'
            ])
            ->leftJoin('p.type_1', 't1')
            ->leftJoin('t1.weaknesses', 't1w')
            ->leftJoin('p.type_2', 't2')
            ->leftJoin('t2.weaknesses', 't2w')
            ->leftJoin('p.ability_1', 'a1')
            ->leftJoin('p.ability_2', 'a2')
            ->leftJoin('p.ability_hidden', 'ah')
            ->leftJoin('p.tier', 't')
            ->leftJoin('p.base_form', 'bf')
            ->leftJoin('p.forms', 'f')
            ->leftJoin('p.preEvo', 'pe')
            ->leftJoin('p.evolutions', 'e');

        return $query;
    }

    protected function baseListQuery(): QueryBuilder
    {
        $query = $this->createQueryBuilder('p')
            ->addSelect(['t1', 't2', 'a1', 'a2', 'ah', 't'])
            ->leftJoin('p.type_1', 't1')
            ->leftJoin('p.type_2', 't2')
            ->leftJoin('p.ability_1', 'a1')
            ->leftJoin('p.ability_2', 'a2')
            ->leftJoin('p.ability_hidden', 'ah')
            ->leftJoin('p.tier', 't')
            ->andWhere('p.deleted IS NULL OR p.deleted != 1');

        return $query;
    }

    public function findAllWithGen($gen)
    {
        return $this->baseListQuery($gen)
            ->andWhere('p.gen = :gen')
            ->setParameter('gen', $gen)
            ->orderBy('p.pokedex', 'ASC')
            ->addOrderBy('p.base_form', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOne($id): ?Pokemon
    {
        return $this->baseCompleteQuery()
            ->andWhere('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneByName(string $value, $gen): ?Pokemon
    {
        return $this->baseCompleteQuery()
            ->andWhere('p.gen = :gen')
            ->setParameter('gen', $gen)
            ->andWhere('p.name LIKE :value OR p.usageName LIKE :value')
            ->setParameter('value', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** to find old gen from current gen id */
    public function findOneForAnotherGen($usageName, $gen): ?Pokemon
    {
        $query = $this->baseCompleteQuery()
            ->andWhere('p.gen = :gen')
            ->setParameter('gen', $gen)
            ->andWhere('p.usageName = :usageName')
            ->setParameter('usageName', $usageName);

        return $query->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string $value Pokemon's name
     * @param int|null $gen desired generation
     * @return array
     */
    public function search($value, $gen)
    {
        // not use baseQuery
        $query = $this->baseListQuery()
            ->andWhere("REPLACE(p.nom,'-',' ') LIKE :search OR REPLACE(p.name,'-',' ') LIKE :search")
            ->andWhere('p.gen = :gen')
            ->setParameter('search', "%$value%")
            ->setParameter('gen', $gen);

        return $query->orderBy('IFNULL(p.nom, p.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByType(Type $type)
    {
        return $this->baseListQuery()
            ->andWhere('p.type_1 = :type OR p.type_2 = :type')
            ->andWhere('p.gen = :gen')
            ->setParameter('type', $type)
            ->setParameter('gen', $type->getGen())
            ->orderBy('IFNULL(p.nom, p.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTier(Tier $tier, ?bool $technically = false)
    {
        // not use base query
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.tier = :tier')
            ->andWhere('p.technically = :tech')
            ->andWhere('p.deleted IS NULL OR p.deleted != 1')
            ->setParameter('tier', $tier)
            ->setParameter('tech', $technically);

        return $query->orderBy('IFNULL(p.nom, p.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDoublesTier(Tier $tier)
    {
        // not use base query
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.deleted IS NULL OR p.deleted != 1')
            ->andWhere('p.doublesTier = :tier')
            ->setParameter('tier', $tier);

        return $query->orderBy('IFNULL(p.nom, p.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByTierBl(Tier $tier)
    {
        // not use base query
        $query = $this->createQueryBuilder('p')
            ->andWhere('p.deleted IS NULL OR p.deleted != 1')
            ->leftJoin('p.tier', 'pt')
            ->andWhere('pt.parent = :tier')
            ->setParameter('tier', $tier);

        return $query->orderBy('IFNULL(p.nom, p.name)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * $gen come from given tier id
     */
    public function findByTierUsage(Tier $tier, ?bool $technically = false): array
    {
        // not use base query
        $query = $this->createQueryBuilder('p')
            ->select('tu')
            ->addSelect(['p', 't1', 't2', 'a1', 'a2', 'ah', 't'])
            ->leftJoin('p.type_1', 't1')
            ->leftJoin('p.type_2', 't2')
            ->leftJoin('p.ability_1', 'a1')
            ->leftJoin('p.ability_2', 'a2')
            ->leftJoin('p.ability_hidden', 'ah')
            ->leftJoin('p.tier', 't')
            ->leftJoin(TierUsage::class, 'tu', Join::WITH, 'tu.pokemon = p.id')
            ->andWhere('tu.tier = :tier')
            ->andWhere('p.deleted IS NULL OR p.deleted != 1')
            ->setParameter('tier', $tier);
        // ->andWhere('(p.tier = :tier AND p.technically = :tech) OR p.tier != :tier')
        // ->setParameter('tech', $technically);
        // rank is ASC
        if ($technically) {
            $query->andWhere('p.tier = :tier AND p.technically = 1');
            // ->setParameter('rank', $tier->getRank()); /*  OR :rank IS NULL */
        }
        $query->orderBy('tu.rank', 'ASC');
        // ->orderBy('IFNULL(p.nom, p.name)', 'ASC');
        $result = $query->getQuery()->getResult();
        return array_values(array_filter($result, fn ($el) => $el instanceof TierUsage));
    }

    /**
     * Custom because pokemons have several ability attrributes
     */
    public function findByAbility(Ability $ability): array
    {
        $gen = $ability->getGen();
        /** @var TierRepository */
        $tierRepo = $this->getEntityManager()->getRepository(Tier::class);
        $vgc = $tierRepo->findOneByNameAndGen('VGC', $gen);
        $bss = $tierRepo->findOneByNameAndGen('BSS', $gen);

        $query = $this->createQueryBuilder('p')
            ->select(['p', 'ua', 'tu', 't1', 't2', 't', 'tp'])
            ->addSelect("(
                CASE WHEN(ua.percent IS NOT NULL) 
                THEN t.sortOrder
                ELSE 9999 END
            ) AS HIDDEN not_null_order")
            ->addSelect('-t.sortOrder AS HIDDEN inverse_order')
            ->leftJoin('p.tier', 't')
            ->leftJoin('t.parent', 'tp')
            ->leftJoin('p.type_1', 't1')
            ->leftJoin('p.type_2', 't2')
            ->leftJoin(
                TierUsage::class,
                'tu',
                Join::WITH,
                'tu.pokemon = p.id AND tu.tier = IFNULL(tp.id, t.id)'
            )
            ->leftJoin(
                UsageAbility::class,
                'ua',
                Join::WITH,
                'ua.tierUsage = tu.id AND ua.ability = :ability'
            )
            ->andWhere('p.deleted IS NULL OR p.deleted != 1')
            ->andWhere('p.ability_1 = :ability OR p.ability_2 = :ability OR p.ability_hidden = :ability')
            ->setParameter('ability', $ability);

        if (!empty($vgc)) {
            $query->addSelect(['uaVgc', 'tuVgc'])
                ->leftJoin(
                    TierUsage::class,
                    'tuVgc',
                    Join::WITH,
                    'tuVgc.pokemon = p.id AND tuVgc.tier = :vgc'
                )
                ->leftJoin(
                    UsageAbility::class,
                    'uaVgc',
                    Join::WITH,
                    'uaVgc.tierUsage = tuVgc.id AND uaVgc.ability = :ability'
                )
                ->setParameter('vgc', $vgc);
        }

        if (!empty($bss)) {
            $query->addSelect(['uaBss', 'tuBss'])
                ->leftJoin(
                    TierUsage::class,
                    'tuBss',
                    Join::WITH,
                    'tuBss.pokemon = p.id AND tuBss.tier = :bss'
                )
                ->leftJoin(
                    UsageAbility::class,
                    'uaBss',
                    Join::WITH,
                    'uaBss.tierUsage = tuBss.id AND uaBss.ability = :ability'
                )
                ->setParameter('bss', $bss);
        }

        $query->addOrderBy('not_null_order', 'ASC')
            ->addOrderBy('ua.percent', 'DESC');
        if (!empty($vgc)) $query->addOrderBy('uaVgc.percent', 'DESC');
        if (!empty($bss)) $query->addOrderBy('uaBss.percent', 'DESC');
        $query->addOrderBy('inverse_order', 'DESC')
            ->addOrderBy('IFNULL(p.nom, p.name)', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function findByMove(Move $move)
    {
        $gen = $move->getGen();
        /** @var TierRepository */
        $tierRepo = $this->getEntityManager()->getRepository(Tier::class);
        $vgc = $tierRepo->findOneByNameAndGen('VGC', $gen);
        $bss = $tierRepo->findOneByNameAndGen('BSS', $gen);

        $query = $this->createQueryBuilder('p')
            ->select(['p', 'um', 'tu', 't1', 't2', 't', 'tp'])
            ->addSelect("(
                CASE WHEN(um.percent IS NOT NULL) 
                THEN t.sortOrder
                ELSE 9999 END
            ) AS HIDDEN not_null_order")
            ->addSelect('-t.sortOrder AS HIDDEN inverse_order')
            ->leftJoin('p.tier', 't')
            ->leftJoin('t.parent', 'tp')
            ->leftJoin('p.type_1', 't1')
            ->leftJoin('p.type_2', 't2')
            ->leftJoin(
                PokemonMove::class,
                'pm',
                Join::WITH,
                'pm.pokemon = p.id AND pm.move = :move'
            )
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
                'um.tierUsage = tu.id AND um.move = :move'
            )
            ->andWhere('p.deleted IS NULL OR p.deleted != 1')
            ->andWhere('pm.id IS NOT NULL')
            ->setParameter('move', $move);

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
                    'umVgc.tierUsage = tuVgc.id AND umVgc.move = :move'
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
                    'umBss.tierUsage = tuBss.id AND umBss.move = :move'
                )
                ->setParameter('bss', $bss);
        }

        $query->addOrderBy('not_null_order', 'ASC')
            ->addOrderBy('um.percent', 'DESC');
        if (!empty($vgc)) $query->addOrderBy('umVgc.percent', 'DESC');
        if (!empty($bss)) $query->addOrderBy('umBss.percent', 'DESC');
        $query->addOrderBy('inverse_order', 'DESC')
            ->addOrderBy('IFNULL(p.nom, p.name)', 'ASC');

        return $query->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function findByItem(Item $item)
    {
        $gen = $item->getGen();
        /** @var TierRepository */
        $tierRepo = $this->getEntityManager()->getRepository(Tier::class);
        $vgc = $tierRepo->findOneByNameAndGen('VGC', $gen);
        $bss = $tierRepo->findOneByNameAndGen('BSS', $gen);

        $query = $this->createQueryBuilder('p')
            ->select(['p', 'ui', 'tu', 't1', 't2', 't', 'tp'])
            ->addSelect("(
                CASE WHEN(ui.percent IS NOT NULL) 
                THEN t.sortOrder
                ELSE 9999 END
            ) AS HIDDEN not_null_order")
            ->addSelect('-t.sortOrder AS HIDDEN inverse_order')
            ->addSelect("(
                CASE WHEN(t.rank IS NOT NULL) 
                THEN (
                    CASE WHEN(t.rank = 3) 
                    THEN 0 
                    ELSE t.rank END
                ) ELSE 9999 END
            ) AS HIDDEN rank2")
            ->leftJoin('p.tier', 't')
            ->leftJoin('t.parent', 'tp')
            ->leftJoin('p.type_1', 't1')
            ->leftJoin('p.type_2', 't2')
            ->leftJoin(
                TierUsage::class,
                'tu',
                Join::WITH,
                'tu.pokemon = p.id AND tu.tier = IFNULL(tp.id, t.id)'
            )
            ->leftJoin(
                UsageItem::class,
                'ui',
                Join::WITH,
                'ui.tierUsage = tu.id AND ui.item = :item'
            )
            ->andWhere('p.deleted IS NULL OR p.deleted != 1')
            ->andWhere('t.rank IS NOT NULL')
            ->setParameter('item', $item);

        if (!empty($vgc)) {
            $query->addSelect(['uiVgc', 'tuVgc'])
                ->leftJoin(
                    TierUsage::class,
                    'tuVgc',
                    Join::WITH,
                    'tuVgc.pokemon = p.id AND tuVgc.tier = :vgc'
                )
                ->leftJoin(
                    UsageItem::class,
                    'uiVgc',
                    Join::WITH,
                    'uiVgc.tierUsage = tuVgc.id AND uiVgc.item = :item'
                )
                ->setParameter('vgc', $vgc);
        }
        if (!empty($bss)) {
            $query->addSelect(['uiBss', 'tuBss'])
                ->leftJoin(
                    TierUsage::class,
                    'tuBss',
                    Join::WITH,
                    'tuBss.pokemon = p.id AND tuBss.tier = :bss'
                )
                ->leftJoin(
                    UsageItem::class,
                    'uiBss',
                    Join::WITH,
                    'uiBss.tierUsage = tuBss.id AND uiBss.item = :item'
                )
                ->setParameter('bss', $bss);
        }

        $query->andWhere(
            "ui.id IS NOT NULL"
                . (!empty($vgc) ? " OR uiVgc.id IS NOT NULL" : '')
                . (!empty($bss) ? " OR uiBss.id IS NOT NULL" : '')
        );

        $query->addOrderBy('not_null_order', 'ASC')
            ->addOrderBy('ui.percent', 'DESC');
        if (!empty($vgc)) $query->addOrderBy('uiVgc.percent', 'DESC');
        if (!empty($bss)) $query->addOrderBy('uiBss.percent', 'DESC');
        $query->addOrderBy('inverse_order', 'DESC')
            ->addOrderBy('IFNULL(p.nom, p.name)', 'ASC');

        return $query->getQuery()->getResult();
    }
}
