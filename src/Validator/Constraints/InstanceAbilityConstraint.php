<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class InstanceAbilityConstraint extends Constraint
{
    public $wrongAbilityMessage   = "Le Pokémon {{ pokemon }} ne peut pas avoir le talent {{ ability }}.";
    public $unknownAbilityMessage = "Le talent {{ name }} du Pokémon {{ pokemon }} n'existe pas en génération {{ gen }}.";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
