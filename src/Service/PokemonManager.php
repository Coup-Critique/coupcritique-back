<?php

namespace App\Service;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use App\Repository\TierUsageRepository;
use App\Repository\TypeRepository;

class PokemonManager
{
    public function __construct(
        protected PokemonRepository $pokemonRepository,
        protected TypeRepository $typeRepository,
        protected TierUsageRepository $tierUsageRepository,
        protected WeaknessManager $weaknessManager
    ) {
    }

    public function buildOnePokemonReturn(Pokemon $pokemon): array
    {
        $id = $pokemon->getId();

        // Prefeed ORM
        $this->typeRepository->findAllByGen($pokemon->getGen());

        if (!empty($pokemon->getType2())) {
            $weaknesses = $this->weaknessManager->mergeWeaknesses(
                $pokemon->getType1()->getWeaknesses(),
                $pokemon->getType2()->getWeaknesses()
            );
        } else {
            $weaknesses = $pokemon->getType1()->getWeaknesses();
        }

        // feed under pokemons
        $baseForm = $pokemon->getBaseForm();
        $prevo = $pokemon->getPreEvo();
        $this->requestPokemonForms($pokemon);

        if (!empty($baseForm)) {
            $this->pokemonRepository->findOne($baseForm->getId());
            // take parent prevo
            if (empty($prevo)) $prevo = $baseForm->getPreEvo();

            if (!empty($baseForm->getBaseForm())) {
                // replace baseForm by parent baseForm
                $parentBaseForm = $this->pokemonRepository->findOne($baseForm->getBaseForm()->getId());
                // take parent's parent prevo
                if (empty($prevo)) $prevo = $parentBaseForm->getPreEvo();

                if ($baseForm->getDeleted()) {
                    $pokemon->setBaseForm($parentBaseForm);
                }
                $this->requestPokemonForms($parentBaseForm, $id, $baseForm->getId());
            } else {
                $this->requestPokemonForms($baseForm, $id);
            }
        }

        if (!empty($prevo)) {
            $this->pokemonRepository->findOne($prevo->getId());
            if (!empty($prevo->getPreEvo())) {
                $this->pokemonRepository->findOne($prevo->getPreEvo()->getId());
            }
        }

        foreach ($pokemon->getEvolutions() as $evo) {
            $this->pokemonRepository->findOne($evo->getId());
            foreach ($evo->getEvolutions() as $upperEvo) {
                $this->pokemonRepository->findOne($upperEvo->getId());
            }
        }

        $inherit = false;
        $usages = $this->tierUsageRepository->findByPokemon($id);
        $differentFromBaseForm = $baseForm == null || $this->checkFormName($pokemon->getName());
        if (
            empty($usages)
            && !$differentFromBaseForm
            && $pokemon->getTier()->getId() === $baseForm->getTier()->getId()
        ) {
            $usages = $this->tierUsageRepository->findByPokemon($baseForm->getId());
            if (!empty($usages)) {
                $inherit = true;
            }
        }

        return [
            'pokemon' => $pokemon,
            'usages' => $usages,
            'weaknesses' => $weaknesses,
            'inherit' => $inherit
        ];
    }

    public function requestPokemonForms(Pokemon $pokemon, ?int $thisId = null, ?int $thisParentId = null)
    {
        foreach ($pokemon->getForms() as $form) {
            if ($form->getId() == $thisId || $form->getId() == $thisParentId) {
                $this->pokemonRepository->findOne($form->getId());
            }
            if ($form->getId() != $thisId) {
                foreach ($form->getForms() as $underForm) {
                    if (!$underForm->getDeleted()) {
                        if ($underForm->getId() != $thisId) {
                            $this->pokemonRepository->findOne($underForm->getId());
                        }
                        if ($form->getDeleted()) {
                            $pokemon->addForm($underForm);
                        }
                    } else {
                        $form->removeForm($underForm);
                    }
                }
            }
            if ($form->getDeleted()) {
                $pokemon->removeForm($form);
            }
        }
    }

    public static function checkFormName(string $name): bool
    {
        // regions and types
        if (preg_match('/(-Alola|-Hisui|-Galar|-Paldea|-Fire|-Water|-Grass|-Bug|-Ice|-Electric|-Poison|-Flying|-Rock|-Ground|-Steel|-Fighting|-Psychic|-Dark|-Ghost|-Fairy|-Dragon)/', $name, $matches)) {
            return true;
        }
        return false;
    }
}
