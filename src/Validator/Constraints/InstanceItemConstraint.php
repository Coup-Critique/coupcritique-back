<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class InstanceItemConstraint extends Constraint
{
    public $unknownItemMessage = "L'objet {{ name }} du Pokémon {{ pokemon }} n'existe pas en génération {{ gen }}.";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
