<?php

namespace App\Validator\Constraints;

use App\Entity\PokemonSet;
use App\Entity\Tier;
use App\Repository\TierRepository;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PokemonSetTierConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly TierRepository $tierRepository)
    {
    }

    /**
     * @param PokemonSet $pokemonSet
     * @param Constraint $constraint
     */
    public function validate($pokemonSet, Constraint $constraint)
    {
        if (!$constraint instanceof PokemonSetTierConstraint) {
            throw new UnexpectedTypeException($constraint, PokemonSetTierConstraint::class);
        }
        if (!$pokemonSet instanceof PokemonSet) {
            throw new UnexpectedTypeException($pokemonSet, PokemonSet::class);
        }
        if (!$pokemonSet->getTier() instanceof Tier) return;

        // reload tier from repo to fix missing values from lazy loading
        $gen = $pokemonSet->getInstance()->getGen();
        $tier = $this->tierRepository->search($pokemonSet->getTier()->getName(), $gen)[0];

        $pokemon         = $pokemonSet->getInstance()->getPokemon();
        $pokemonTierRank = empty($pokemon->getTier())
            ? null
            : $pokemon->getTier()->getRank();

        $rank = $tier->getRank();
        if ($tier->getShortName() === "ZU") {
            $rank--;
        }

        if (
            !is_null($rank)
            && !is_null($pokemonTierRank)
            && $pokemonTierRank < $rank
            && $rank < 50
        ) {
            $this->context->buildViolation($constraint->wrongTierMessage)
                ->setParameters([
                    '{{ tier }}'    => $tier->getName(),
                    '{{ pokemon }}' => $pokemon->getNom() ?: $pokemon->getName(),
                    '{{ gen }}' => $gen
                ])
                ->atPath("export")
                ->addViolation();
        }
    }
}
