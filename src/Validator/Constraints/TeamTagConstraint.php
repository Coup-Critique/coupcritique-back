<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TeamTagConstraint extends Constraint
{
    public $missingRightsMessage = "Le tag {{ pokemon }} n'est pas disponible pour vous.";
    public $tooMuchMessage = "Les étiquettes \"hyper offense\", \"bulky offense\", \"balanced\" et \"stall\" ne peuvent pas être attribuées en même temps à une même équipe.";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
