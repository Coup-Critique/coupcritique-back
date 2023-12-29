<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class GenConstraint extends Constraint
{
    public $genMessage = "La génération {{ gen }} n'est pas disponible.";

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
