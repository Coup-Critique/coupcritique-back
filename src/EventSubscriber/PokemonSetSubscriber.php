<?php

namespace App\EventSubscriber;

use App\DTO\Message\SetDiscordMessage;
use App\Entity\PokemonSet;
use App\Entity\User;
use App\EventSubscriber\Abstracts\AbstractMessengerSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class PokemonSetSubscriber extends AbstractMessengerSubscriber
{
    protected function getEntity(LifecycleEventArgs $args): ?PokemonSet
    {
        $entity = $args->getObject();
        if (!$entity instanceof PokemonSet) return null;
        return $entity;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $this->getEntity($args);
        if ($entity == null) return;


        if (!empty($entity->getContent())) {
            $this->sendDiscordMessage(SetDiscordMessage::POST, $entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $this->getEntity($args);
        if ($entity == null) return;

        if (!$args->hasChangedField('content')) return;
        $prevContent = $args->getOldValue('content');

        if ($this->isUpdateSufficient($prevContent, $entity->getContent())) {
            $this->sendDiscordMessage(SetDiscordMessage::PUT, $entity);
        }
    }

    protected function sendDiscordMessage(string $method, PokemonSet $entity): void
    {
        if ($this->request == null) return;
        /** @var User $user */
        $user = $this->security->getUser();
        $message = new SetDiscordMessage($this->request->getSchemeAndHttpHost(), $method, $user, $entity);
        $this->sendChatMessage($message);
    }
}
