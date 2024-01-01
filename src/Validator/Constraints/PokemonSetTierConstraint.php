<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PokemonSetTierConstraint extends Constraint
{
    public $wrongTierMessage = "Le Pokémon {{ pokemon }} est banni du tier {{ tier }} ou incompatible avec la génération {{ gen }}.";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
