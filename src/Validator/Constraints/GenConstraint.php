<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class GenConstraint extends Constraint
{
    public $genMessage = "La génération {{ gen }} n'est pas disponible.";

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
