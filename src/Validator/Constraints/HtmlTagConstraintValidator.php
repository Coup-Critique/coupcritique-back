<?php

namespace App\Validator\Constraints;

use JsonException;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class HtmlTagConstraintValidator extends ConstraintValidator
{
    /** @var string $projectDir */
    protected $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @param string $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof HtmlTagConstraint) {
            throw new UnexpectedTypeException($constraint, HtmlTagConstraint::class);
        }

        if (is_null($value)) return;
        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $banTags = ['<script', 'iframe', 'javascript', 'applet', 'embedded', 'php', '.js'];

        $matches = [];
        foreach ($banTags as $banTag) {
            if (stripos($value, $banTag) !== false) {
                $matches[] = trim($banTag);
                break;
            }
        }
        if (count($matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameters(["{{ banTags }}" => implode(', ', $matches)])
                ->addViolation();
        }
    }
}
