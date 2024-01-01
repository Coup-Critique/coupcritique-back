<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InstanceMoveConstraint extends Constraint
{
	public $unknownMoveMessage    = "La capacité {{ move }} du Pokémon {{ pokemon }} n'existe pas en génération {{ gen }}.";
	public $wrongMoveMessage      = "Le Pokémon {{ pokemon }} n'apprend pas la capacité {{ move }}.";
	public $duplicatedMoveMessage = "Un Pokémon ne peut pas avoir plusieurs fois la même capacité. Le Pokémon {{ pokemon }} à au moins 2 fois la capacité {{ move }} dans son set.";

	public function getTargets(): string
	{
		return self::CLASS_CONSTRAINT;
	}
}
