<?php

namespace App\Validator\Constraints;

use App\Entity\Ability;
use App\Entity\Pokemon;
use App\Entity\PokemonInstance;
use App\Repository\AbilityRepository;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InstanceAbilityConstraintValidator extends ConstraintValidator
{
    private $abilityRepository;

    public function __construct(AbilityRepository $abilityRepository)
    {
        $this->abilityRepository = $abilityRepository;
    }

    /**
     * @param PokemonInstance $pkm_inst
     * @param Constraint $constraint
     */
    public function validate($pkm_inst, Constraint $constraint)
    {
        if (!$constraint instanceof InstanceAbilityConstraint) {
            throw new UnexpectedTypeException($constraint, InstanceAbilityConstraint::class);
        }
        if (!$pkm_inst instanceof PokemonInstance) {
            throw new UnexpectedTypeException($pkm_inst, PokemonInstance::class);
        }

        $pokemon = $pkm_inst->getPokemon();
        if (!$pokemon instanceof Pokemon || !$pkm_inst->getPokemon()->getId()) return;

        if (empty($pokemon->getAbility1())) {
            if (!empty($pkm_inst->getAbility()) && $pkm_inst->getAbility()->getName() !== 'No Ability') {
                $ability = $pkm_inst->getAbility();
                $this->context->buildViolation($constraint->wrongAbilityMessage)
                    ->setParameters(
                        [
                            "{{ ability }}" => $ability->getNom() ?: $ability->getName(),
                            "{{ pokemon }}" => $pokemon->getNom() ?: $pokemon->getName()
                        ]
                    )
                    ->atPath("export")
                    ->addViolation();
            }
            return;
        }
        if (
            empty($pkm_inst->getAbility()) 
            || is_null($pkm_inst->getAbility()->getName())
        ) {
            $pkm_inst->setAbility($pokemon->getAbility1());
        }
        // after setAbility
        $ability = $this->abilityRepository->findOneByNameAndGen($pkm_inst->getAbility()->getName(), $pkm_inst->getGen());
        if (empty($ability)) {
            $this->context->buildViolation($constraint->unknownAbilityMessage)
                ->setParameters(
                    [
                        "{{ name }}" => $pkm_inst->getAbility()->getName(),
                        "{{ pokemon }}" => $pokemon->getNom() ?: $pokemon->getName(),
                        "{{ gen }}" => $pkm_inst->getGen()
                    ]
                )
                ->atPath("export")
                ->addViolation();
            return;
        }

        $abilities = [$pokemon->getAbility1()->getId()];
        if ($pokemon->getAbility2()) {
            $abilities[] = $pokemon->getAbility2()->getId();
        }
        if ($pokemon->getAbilityHidden()) {
            $abilities[] = $pokemon->getAbilityHidden()->getId();
        }
        if (!empty($pokemon->getBaseForm())) {
            $baseForm = $pokemon->getBaseForm();
            $abilities[] = $baseForm->getAbility1()->getId();
            if ($baseForm->getAbility2()) {
                $abilities[] = $baseForm->getAbility2()->getId();
            }
            if ($baseForm->getAbilityHidden()) {
                $abilities[] = $baseForm->getAbilityHidden()->getId();
            }
        }

        if (!in_array($ability->getId(), $abilities))
            $this->context->buildViolation($constraint->wrongAbilityMessage)
                ->setParameters(
                    [
                        "{{ ability }}" => $ability->getNom() ?: $ability->getName(),
                        "{{ pokemon }}" => $pokemon->getNom() ?: $pokemon->getName()
                    ]
                )
                ->atPath("export")
                ->addViolation();
    }
}
