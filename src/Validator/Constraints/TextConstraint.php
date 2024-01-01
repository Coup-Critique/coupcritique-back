<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;


#[\Attribute]
class TextConstraint extends Constraint
{
    public $message = "Ce texte n'est pas acceptable car il contient le ou les mots : {{ banWords }}.";

    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
