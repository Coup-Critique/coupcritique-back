<?php

namespace App\Validator\Constraints;

use App\Entity\Item;
use App\Entity\Pokemon;
use App\Entity\PokemonInstance;
use App\Repository\ItemRepository;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InstanceItemConstraintValidator extends ConstraintValidator
{
    public function __construct(private readonly ItemRepository $itemRepository)
    {
    }

    /**
     * @param PokemonInstance $pkm_inst
     */
    public function validate($pkm_inst, Constraint $constraint): void
    {
        if (!$constraint instanceof InstanceItemConstraint) {
            throw new UnexpectedTypeException($constraint, InstanceItemConstraint::class);
        }
        if (!$pkm_inst instanceof PokemonInstance) {
            throw new UnexpectedTypeException($pkm_inst, PokemonInstance::class);
        }

        $pokemon = $pkm_inst->getPokemon();
        if (!$pokemon instanceof Pokemon || !$pkm_inst->getPokemon()->getId()) return;
        //Item is not mandatory
        if (empty($pkm_inst->getItem())) return;
        if (is_null($pkm_inst->getItem()->getName())) {
            $pkm_inst->setItem(null);
            return;
        }
        /**
         * If a pokemon instance helds an item
         * make sure that it exists, and it is compatible
         * with the generation
         */
        $item = $this->itemRepository->findOneByNameAndGen($pkm_inst->getItem()->getName(), $pkm_inst->getGen());
        if (empty($item)) {
            $this->context->buildViolation($constraint->unknownItemMessage)
                ->setParameters(
                    [
                        "{{ name }}" => $pkm_inst->getItem()->getName(),
                        "{{ pokemon }}" => $pokemon->getNom() ?: $pokemon->getName(),
                        "{{ gen }}" => $pkm_inst->getGen()
                    ]
                )
                ->atPath("export")
                ->addViolation();
            return;
        }
    }
}
