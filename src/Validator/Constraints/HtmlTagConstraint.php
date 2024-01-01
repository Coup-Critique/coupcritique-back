<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class HtmlTagConstraint extends Constraint
{
    public $message = "Ce contenu n'est pas acceptable pour des contraintes de sécurité, car il contient les termes suivants : {{ banTags }}.";
    
    public function getTargets(): string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
