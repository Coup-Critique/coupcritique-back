<?php

namespace App\Validator\Constraints;

use App\Entity\Move;
use App\Entity\Pokemon;
use App\Entity\PokemonInstance;
use App\Repository\MoveRepository;
use Symfony\Component\Intl\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InstanceMoveConstraintValidator extends ConstraintValidator
{
    public function __construct(protected MoveRepository $moveRepository)
    {
    }

    /**
     * @param PokemonInstance $pkm_inst
     */
    public function validate($pkm_inst, Constraint $constraint): void
    {
        if (!$constraint instanceof InstanceMoveConstraint) {
            throw new UnexpectedTypeException($constraint, InstanceMoveConstraint::class);
        }
        if (!$pkm_inst instanceof PokemonInstance) {
            throw new UnexpectedTypeException($pkm_inst, PokemonInstance::class);
        }
        if (!$pkm_inst->getPokemon() instanceof Pokemon || !$pkm_inst->getPokemon()->getId()) return;

        $moves = [
            $pkm_inst->getMove1(),
            $pkm_inst->getMove2(),
            $pkm_inst->getMove3(),
            $pkm_inst->getMove4()
        ];

        $this->checkMovesLearned($pkm_inst, $moves, $constraint);
        $this->checkMovesDuplicated($pkm_inst, $moves, $constraint);
    }

    /**
     * Check if the array of moves doesn't contain duplicates
     * @param Move[] $moves
     */
    private function checkMovesDuplicated(PokemonInstance $pkm_inst, array $moves, Constraint $constraint): void
    {
        $counter = [];
        foreach ($moves as $i => $move) {
            if (!$move instanceof Move) continue;
            $move_name = $move->getName();
            if (array_key_exists($move_name, $counter)) {
                $this->context->buildViolation($constraint->duplicatedMoveMessage)
                    ->setParameters(
                        [
                            "{{ move }}"    => $move->getNom() ?: $move_name,
                            "{{ pokemon }}" => $pkm_inst->getPokemon()->getNom()
                                ?: $pkm_inst->getPokemon()->getName()
                        ]
                    )
                    ->addViolation();
            } else {
                $counter[$move_name] = true;
            }
        }
    }

    /**
     * Check if each move in the array is compatible with the Pokemon's movepool
     * @param Move[] $moves
     */
    private function checkMovesLearned(PokemonInstance $pkm_inst, array $moves, Constraint $constraint): void
    {
        $pokemon  = $pkm_inst->getPokemon();
        $movepool = $this->moveRepository->findByPokemon($pokemon);
        // if (empty($movepool) && $pokemon->getBaseForm() instanceof Pokemon) {
        //     $movepool = $this->moveRepository->findByPokemon($pokemon->getBaseForm());
        // }
        // $this->addPreEvoMoves($pokemon, $movepool);
        foreach ($moves as $i => $move) {
            if (empty($move)) continue;
            if (!is_null($move->getName())) {
                $move = $this->moveRepository->findOneByNameAndGen($move->getName(), $pkm_inst->getGen());
            }
            if (empty($move) || is_null($move->getName())) {
                $this->context->buildViolation($constraint->unknownMoveMessage)
                    ->setParameters(
                        [
                            "{{ move }}"    => "numéro " . ($i + 1),
                            "{{ pokemon }}" => $pkm_inst->getPokemon()->getNom()
                                ?: $pkm_inst->getPokemon()->getName(),
                            "{{ gen }}" => $pkm_inst->getGen()
                        ]
                    )
                    ->addViolation();
            } /* elseif (!$this->containsMove($movepool, $move)) {
                $this->context->buildViolation($constraint->wrongMoveMessage)
                    ->setParameters(
                        [
                            "{{ move }}"    => $move->getNom() ?: $move->getName(),
                            "{{ pokemon }}" => $pkm_inst->getPokemon()->getNom()
                                ?: $pkm_inst->getPokemon()->getName()
                        ]
                    )
                    ->atPath("export")
                    ->addViolation();
            } */
        }
    }

    /**
     * @param Move[] $movepool // passed by reference
     */
    public function addPreEvoMoves(Pokemon $pokemon, array &$movepool): void
    {
        $preEvo = $pokemon->getPreEvo()
            ?: ($pokemon->getBaseForm()
                ? $pokemon->getBaseForm()->getPreEvo()
                : null);
        if (!is_null($preEvo)) {
            foreach ($this->moveRepository->findByPokemon($preEvo) as $move) {
                if (!$this->containsMove($movepool, $move)) {
                    $movepool[] = $move;
                }
            }
            $this->addPreEvoMoves($preEvo, $movepool);
        }
    }

    /**
     * @param Move[] $moves
     */
    public function containsMove(array $moves, Move $move): bool
    {
        foreach ($moves as $m) {
            if ($m->getId() === $move->getId()) return true;
        }
        return false;
    }
}
