<?php

namespace App\Validator\Constraints;

use App\Entity\Nature;
use App\Entity\Pokemon;
use App\Entity\PokemonInstance;
use App\Repository\NatureRepository;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InstanceNatureConstraintValidator extends ConstraintValidator
{
    /** @var NatureRepository */
    protected $natureRepository;

    public function __construct(NatureRepository $natureRepository)
    {
        $this->natureRepository = $natureRepository;
    }

    /**
     * @param PokemonInstance $pkm_inst
     * @param Constraint $constraint
     */
    public function validate($pkm_inst, Constraint $constraint)
    {
        if (!$constraint instanceof InstanceNatureConstraint) {
            throw new UnexpectedTypeException($constraint, InstanceNatureConstraint::class);
        }
        if (!$pkm_inst instanceof PokemonInstance) {
            throw new UnexpectedTypeException($pkm_inst, PokemonInstance::class);
        }

        $pokemon = $pkm_inst->getPokemon();
        if (!$pokemon instanceof Pokemon || !$pkm_inst->getPokemon()->getId()) return;

        $nature = $pkm_inst->getNature();
        if (empty($nature) || is_null($nature->getName())) {
            if ($serious = $this->natureRepository->findOneByName('Serious')) {
                $pkm_inst->setNature($serious);
                $nature = $serious;
            }
        }
        if (!$nature instanceof Nature || !$nature->getName()) {
            $this->context->buildViolation($constraint->unknownNatureMessage)
                ->setParameters(
                    ["{{ pokemon }}" => $pokemon->getNom() ?: $pokemon->getName()]
                )
                ->atPath("export")
                ->addViolation();
        }
    }
}
