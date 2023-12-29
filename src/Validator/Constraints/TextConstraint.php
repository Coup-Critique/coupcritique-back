<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class TextConstraint extends Constraint
{
    public $message = "Ce texte n'est pas acceptable car il contient le ou les mots : {{ banWords }}.";

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
