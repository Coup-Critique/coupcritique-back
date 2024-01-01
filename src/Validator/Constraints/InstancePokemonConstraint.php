<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class InstancePokemonConstraint extends Constraint
{
    public $unknownPokemonMessage = "L'export est invalide ou contient un Pokémon inconnu ou un surnom possédant un caractère spécial comme @ ou des parenthèses.";
    public $uncompatibleGenMessage = "Le Pokémon {{ name }} n'est pas compatible à la génération {{ gen }} ou n'existe pas.";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
