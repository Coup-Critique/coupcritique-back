<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InstanceItemConstraint extends Constraint
{
    public $unknownItemMessage = "L'objet {{ name }} du Pokémon {{ pokemon }} n'existe pas en génération {{ gen }}.";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
