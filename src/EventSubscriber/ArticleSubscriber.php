<?php

namespace App\EventSubscriber;

use App\DTO\Message\ArticleDiscordMessage;
use App\Entity\Abstracts\AbstractArticle;
use App\Entity\User;
use App\EventSubscriber\Abstracts\AbstractMessengerSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ArticleSubscriber extends AbstractMessengerSubscriber
{
    protected function getEntity(LifecycleEventArgs $args): ?AbstractArticle
    {
        $entity = $args->getObject();
        if (!$entity instanceof AbstractArticle) return null;
        return $entity;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $this->getEntity($args);
        if ($entity == null) return;


        if (!empty($entity->getDescription())) {
            $this->sendDiscordMessage(ArticleDiscordMessage::POST, $entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $this->getEntity($args);
        if ($entity == null) return;

        if (!$args->hasChangedField('description')) return;
        $prevDescr = $args->getOldValue('description');

        if ($this->isUpdateSufficient($prevDescr, $entity->getDescription())) {
            $this->sendDiscordMessage(ArticleDiscordMessage::PUT, $entity);
        }
    }

    protected function sendDiscordMessage(string $method, AbstractArticle $entity): void
    {
        if ($this->request == null) return;
        /** @var User $user */
        $user = $this->security->getUser();
        $message = new ArticleDiscordMessage($this->request->getSchemeAndHttpHost(), $method, $user, $entity);
        $this->sendChatMessage($message);
    }
}
