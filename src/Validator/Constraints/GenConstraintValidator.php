<?php

namespace App\Validator\Constraints;

use App\Service\GenRequestManager;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class GenConstraintValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof GenConstraint) {
            throw new UnexpectedTypeException($constraint, GenConstraint::class);
        }

        if (!in_array($value, GenRequestManager::$gens)) {

            $this->context->buildViolation($constraint->genMessage)
                ->setParameters(['{{ gen }}' => $value])
                ->atPath("gen")
                ->addViolation();
        }
    }
}
