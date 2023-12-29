<?php

namespace App\Validator\Constraints;

use App\Entity\Pokemon;
use App\Entity\PokemonInstance;
use App\Repository\PokemonRepository;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InstancePokemonConstraintValidator extends ConstraintValidator
{

    private $pokemonRepository;

    public function __construct(PokemonRepository $pokemonRepository)
    {
        $this->pokemonRepository = $pokemonRepository;
    }

    /**
     * @param PokemonInstance $pkm_inst
     * @param Constraint $constraint
     */
    public function validate($pkm_inst, Constraint $constraint)
    {
        if (!$constraint instanceof InstancePokemonConstraint) {
            throw new UnexpectedTypeException($constraint, InstancePokemonConstraint::class);
        }
        if (!$pkm_inst instanceof PokemonInstance) {
            throw new UnexpectedTypeException($pkm_inst, PokemonInstance::class);
        }

        if (empty($pkm_inst->getPokemon()) || empty($pkm_inst->getPokemon()->getName())) {
            $this->context->buildViolation($constraint->unknownPokemonMessage)
                ->atPath("export")
                ->addViolation();
            return;
        }
        $pokemon = $this->pokemonRepository->findOneByNameAndGen(
            $pkm_inst->getPokemon()->getName(),
            $pkm_inst->getGen()
        );
        if (empty($pokemon)) {
            $this->context->buildViolation($constraint->uncompatibleGenMessage)
                ->setParameters([
                    "{{ name }}" => $pkm_inst->getPokemon()->getName(),
                    "{{ gen }}" => $pkm_inst->getGen()
                ])
                ->atPath("export")
                ->addViolation();
            return;
        }
    }
}
