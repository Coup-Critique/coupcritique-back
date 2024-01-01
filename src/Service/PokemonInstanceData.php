<?php

namespace App\Service;

use App\Entity\PokemonInstance;

class PokemonInstanceData
{
    public static function setDataOldGen(PokemonInstance $pokemonInstance, $gen): void
    {
        if ($gen < 3) {
            $pokemonInstance->setHpIv(30);
            $pokemonInstance->setAtkIv(30);
            $pokemonInstance->setDefIv(30);
            $pokemonInstance->setSpdIv(30);
            $pokemonInstance->setSpaIv(30);
            $pokemonInstance->setSpeIv(30);
            $pokemonInstance->setHpEv(252);
            $pokemonInstance->setAtkEv(252);
            $pokemonInstance->setDefEv(252);
            $pokemonInstance->setSpdEv(252);
            $pokemonInstance->setSpaEv(252);
            $pokemonInstance->setSpeEv(252);
        }
    }
}
