<?php

namespace App\DTO\Message;

use App\Entity\PokemonSet;
use App\Entity\User;

class SetDiscordMessage extends AbstractDiscordMessage
{
    public function __construct(
        string $domain,
        string $method,
        User $user,
        protected PokemonSet $entity
    ) {
        parent::__construct($domain, $method, $user);
    }

    public function getSubject(): string
    {
        return "{$this->getAction()} du set : {$this->entity->getName()} pour {$this->entity->getInstance()->getPokemon()->getNom()}";
    }

    public function getUrl(): string
    {
        return "{$this->domain}/entity/pokemons/{$this->entity->getInstance()->getPokemon()->getId()}";
    }

    public function getContent(): string
    {
        return $this->limitContent($this->entity->getContent());
    }
}
