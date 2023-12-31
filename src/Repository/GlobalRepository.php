<?php

namespace App\Repository;

use Doctrine\ORM\EntityManagerInterface;
use PDO;

class GlobalRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function search(string $search, $gen)
    {
        $NUMBER_OF_PREVIEWS = 5;

        if (str_contains($search, '-')) {
            $search = str_replace('-', ' ', $search);
        }

        $sql_query = 'SELECT * FROM ( 
            SELECT "tiers" AS entity, "Tier" AS category, 1 AS entity_order, name, short_name AS nom, id, gen, 0 AS deleted FROM tier WHERE tier.name <> "Untiered"
            UNION SELECT "types" AS entity, "Type" AS category, 2 AS entity_order, name, nom, id, gen, 0 AS deleted FROM type
            UNION SELECT "pokemons" AS entity, "Pokemon" AS category, 3 AS entity_order, name, nom, id, gen, deleted FROM pokemon 
            UNION SELECT "abilities" AS entity, "Talent" AS category, 4 AS entity_order, name, nom, id, gen, 0 AS deleted FROM ability 
            UNION SELECT "moves" AS entity, "Capacité" AS category, 5 AS entity_order, name, nom, id, gen, 0 AS deleted FROM move 
            UNION SELECT "items" AS entity, "Objet" AS category, 6 AS entity_order, name, nom, id, gen, 0 AS deleted FROM item 
        ) AS research 
        WHERE (
            REPLACE(research.nom,"-"," ") LIKE :search 
            OR REPLACE(research.name,"-"," ") LIKE :search 
        )
        AND research.gen LIKE :gen 
        AND (deleted IS NULL OR deleted != 1)
        ORDER BY entity_order, COALESCE(research.nom, research.name) 
        LIMIT :previews_limit';

        $connection = $this->em->getconnection();
        $stmt       = $connection->prepare($sql_query);
        $stmt->bindValue('search', "%{$search}%", PDO::PARAM_STR);
        $stmt->bindValue('gen', $gen, PDO::PARAM_STR);
        $stmt->bindValue('previews_limit', $NUMBER_OF_PREVIEWS, PDO::PARAM_INT);
        return $stmt->executeQuery()->fetchAllAssociative();
    }
}
