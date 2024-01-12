<?php

namespace App\EventListener\EntityListener;

use App\DTO\Message\SetDiscordMessage;
use App\Entity\PokemonSet;
use App\Entity\User;
use App\EventListener\EntityListener\Abstracts\AbstractMessengerListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: PokemonSet::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: PokemonSet::class)]
class PokemonSetListener extends AbstractMessengerListener
{
    public function postPersist(PokemonSet $pokemonSet, PostPersistEventArgs $args): void
    {
        if (!empty($pokemonSet->getContent())) {
            $this->sendDiscordMessage(SetDiscordMessage::POST, $pokemonSet);
        }
    }

    public function preUpdate(PokemonSet $pokemonSet, PreUpdateEventArgs $args): void
    {
        if (!$args->hasChangedField('content')) return;
        $prevContent = $args->getOldValue('content');

        if ($this->isUpdateSufficient($prevContent, $pokemonSet->getContent())) {
            $this->sendDiscordMessage(SetDiscordMessage::PUT, $pokemonSet);
        }
    }

    protected function sendDiscordMessage(string $method, PokemonSet $pokemonSet): void
    {
        if ($this->request == null) return;
        /** @var User $user */
        $user = $this->security->getUser();
        $message = new SetDiscordMessage($this->request->getSchemeAndHttpHost(), $method, $user, $pokemonSet);
        $this->sendChatMessage($message);
    }
}
