<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TeamInstanceAndTierConstraint extends Constraint
{
    public $unknownTierMessage = "Le tier donné pour l'équipe est inconnu.";
    public $unknownPokemonMessage = "Le Pokémon n°{{ index }} est invalide.";
    public $wrongTierMessage = "Le Pokémon {{ pokemon }} est banni ou indisponible dans le tier {{ tier }}.";
    public $teraTypeMessage = "La Téracristalisation n'est pas autorisée dans le tier {{ tier }} (sur le Pokémon {{ pokemon }}).";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
