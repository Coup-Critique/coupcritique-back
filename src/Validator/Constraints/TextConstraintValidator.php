<?php

namespace App\Validator\Constraints;

use JsonException;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class TextConstraintValidator extends ConstraintValidator
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
        if (!$constraint instanceof TextConstraint) {
            throw new UnexpectedTypeException($constraint, TextConstraint::class);
        }

        if (is_null($value)) return;
        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        try {
            $banWords = json_decode(
                file_get_contents($this->projectDir . '/public/json/banWords.json'),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            return;
        }

        $matches = [];
        foreach ($banWords['short'] as $banWord) {
            foreach ($banWords['pre'] as $pre) {
                $banWordL = $pre . $banWord;
                foreach ($banWords['post'] as $post) {
                    if (stripos($value, $banWordL . $post) !== false) {
                        $matches[] = trim($banWord);
                        break 2;
                    }
                }
            }
        }
        foreach ($banWords['long'] as $banWord) {
            if (stripos($value, $banWord) !== false) {
                $matches[] = trim($banWord);
            }
        }
        if (count($matches)) {
            $this->context->buildViolation($constraint->message)
                ->setParameters(["{{ banWords }}" => implode(', ', $matches)])
                ->addViolation();
        }
    }
}