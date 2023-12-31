<?php

namespace App\Validator\Constraints;

use App\Entity\Pokemon;
use App\Entity\PokemonInstance;
use App\Entity\Team;
use App\Entity\Tier;
use App\Repository\TierRepository;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TeamInstanceAndTierConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly TierRepository $tierRepository)
    {
    }

    /**
     * @param Team $team
     * @param Constraint $constraint
     */
    public function validate($team, Constraint $constraint)
    {
        if (!$constraint instanceof TeamInstanceAndTierConstraint) {
            throw new UnexpectedTypeException($constraint, TeamInstanceAndTierConstraint::class);
        }
        if (!$team instanceof Team) {
            throw new UnexpectedTypeException($team, Team::class);
        }
        if (!$team->getTier() instanceof Tier || is_null($team->getTier()->getId())) {
            $this->context->buildViolation($constraint->unknownTierMessage)
                ->atPath("tier")
                ->addViolation();
        }

        // To counter lazy loading
        $tier = $this->tierRepository->findOneById($team->getTier()->getId());

        $rank = $tier->getRank();
        if ($tier->getShortName() === "ZU") {
            $rank--;
        }

        // Ignore checking for special tiers, such as 1v1
        // if (!is_null($rank)) {
        foreach ($team->getPokemonInstances() as $i => $instance) {
            $pokemon = $instance->getPokemon();
            if (!$pokemon instanceof Pokemon || empty($pokemon->getName())) {
                $this->context->buildViolation($constraint->unknownPokemonMessage)
                    ->setParameters(['{{ index }}' => $i])
                    ->atPath("export")
                    ->addViolation();
            }

            $pokemonTierRank = empty($pokemon->getTier())
                ? null
                : $pokemon->getTier()->getRank();

            if (
                (empty($pokemon->getTier()) || $pokemon->getTier()->getName() === 'Untiered')
                || (!is_null($rank)
                    && !is_null($pokemonTierRank)
                    && $pokemonTierRank < $rank
                    && $rank < 50
                )
            ) {
                $this->context->buildViolation($constraint->wrongTierMessage)
                    ->setParameters(
                        [
                            '{{ tier }}'    => $tier->getName(),
                            '{{ pokemon }}' => $pokemon->getNom() ?: $pokemon->getName()
                        ]
                    )
                    ->atPath("export")
                    ->addViolation();
            }

            if (!is_null($instance->getTera()) && $tier->getTeraBan()) {
                $this->context->buildViolation($constraint->teraTypeMessage)
                    ->setParameters(
                        [
                            '{{ tier }}'    => $tier->getName(),
                            '{{ pokemon }}' => $pokemon->getNom() ?: $pokemon->getName()
                        ]
                    )
                    ->atPath("export")
                    ->addViolation();
            }
        }
        // }
    }
}
