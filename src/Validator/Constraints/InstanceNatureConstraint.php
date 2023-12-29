<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InstanceNatureConstraint extends Constraint
{
    public $unknownNatureMessage = "La nature du Pokémon {{ pokemon }} est inconue.";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
