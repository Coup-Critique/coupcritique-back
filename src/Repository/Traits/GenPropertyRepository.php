<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

/**
 * This trait has the purpose to centralize
 * all utilitary methods that will use
 * the gen property for concerned entities (ability, item, move, pokemon)
 *
 * @author ii02735
 * @package App\Repository 
 */
trait GenPropertyRepository
{
    public function getAvailableGens(string $usageName){
        return $this->createQueryBuilder('e')
            ->select('e.gen AS gen, e.id AS id')
            ->where('e.usageName = :usageName')
            ->setParameter('usageName', $usageName)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the element for a specified gen
     * @param string $value Element's name 
     * @param string|int $gen
     * @return mixed|null 
     */
    public function findOneByNameAndGen(string $value, $gen)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.name = :value OR e.usageName = :value')
            ->andWhere('e.gen = :gen')
            ->setParameter('value', $value)
            ->setParameter('gen', $gen);
        return $query->getQuery()->getOneOrNullResult();
    }

    // /**
    //  * @deprecated
    //  * 
    //  * Returns the element with the specified gen
    //  * @param string $value Element's name 
    //  * @param array $gen gen array
    //  * @return mixed|null 
    //  */
    // public function findOneByNameAndGenArray(string $value, array $gen)
    // {
    //     return $this->createQueryBuilder('e')
    //         ->where('e.name = :value')
    //         ->andWhere('e.gen = :gen')
    //         ->setParameter('value', $value)
    //         ->setParameter('gen', json_encode($gen))
    //         ->getQuery()
    //         ->getOneOrNullResult();
    // }

    // /**
    //  * @deprecated
    //  * 
    //  * Add a where clause in the query, which set a gen regex expression
    //  * @param QueryBuilder $query 
    //  * @param string $alias 
    //  * @param string|int $gen 
    //  * @return QueryBuilder 
    //  */
    // protected function addWhereGenInQuery(QueryBuilder $query, $alias, $gen): QueryBuilder
    // {
    //     return $query
    //         ->andWhere("REGEXP($alias.gen,:genRegExp) = 1")
    //         // Check if after gen is followed by a comma (and not by another digit) or a closing square bracket
    //         ->setParameter('genRegExp', $gen . '(?=,|])');
    // }
}
