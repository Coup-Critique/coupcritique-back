<?php

namespace App\Service;

use App\Entity\Tier;
use App\Entity\TierUsage;
use App\Repository\PokemonRepository;
use App\Repository\TierRepository;
use App\Repository\TierUsageRepository;

class TierManager
{
    public function __construct(
        protected TierRepository $tierRepository,
        protected PokemonRepository $pokemonRepository,
        protected TierUsageRepository $tierUsageRepository
    ) {
    }

    public function getPokemonsFromTier(Tier $tier): array
    {
        $gen = $tier->getGen();

        $pokemons = $this->pokemonRepository->findByTier($tier);
        if (!is_null($tier->getUsageName())) {
            if ($tier->getShortName() === 'ZU') {
                $pu = $this->tierRepository->findOneByNameAndGen('PU', $gen);
                if ($pu) {
                    // gen come from tier
                    $return['usages'] = $this->pokemonRepository->findByTierUsage($tier, true);
                    if (count($return['usages']) == 0) {
                        $return['pokemons'] = $this->pokemonRepository->findByTier($pu, true);
                    }
                }
            } elseif (!is_null($tier->getRank())) {
                $return['usages'] = $this->pokemonRepository->findByTierUsage($tier);
                $return['usagesTechnically'] = $this->pokemonRepository->findByTierUsage($tier, true);
                $return['pokemonsBl'] = $this->pokemonRepository->findByTierBl($tier);
                if (count($return['usages']) == 0) {
                    $return['pokemons'] = $pokemons;
                    $return['pokemonsTechnically'] = $this->pokemonRepository->findByTier($tier, true);
                } else {
                    foreach ($pokemons as $pokemon) {
                        $found = false;
                        foreach ($return['usages'] as $usage) {
                            if ($usage->getPokemon()->getId() === $pokemon->getId()) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $usage = new TierUsage();
                            $usage->setPokemon($pokemon);
                            $usage->setPercent(0);
                            $usage->setTier($pokemon->getTier());
                            $return['usages'][] = $usage;
                        }
                    }
                }
            } else {
                $return['usages'] = $this->tierUsageRepository->findByTier($tier);
                if (count($return['usages']) == 0) {
                    if ($tier->getIsDouble()) {
                        $return['pokemons'] = $this->pokemonRepository->findByDoublesTier($tier);
                    } else {
                        $return['pokemons'] = $pokemons;
                    }
                }
            }
        } else {
            $return['pokemons'] = $pokemons;
            $return['pokemonsTechnically'] = $this->pokemonRepository->findByTier($tier, true);
        }

        return $return;
    }
}
