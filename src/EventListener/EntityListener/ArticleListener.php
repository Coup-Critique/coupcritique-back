<?php

namespace App\EventListener\EntityListener;

use App\DTO\Message\ArticleDiscordMessage;
use App\Entity\Abstracts\AbstractArticle;
use App\Entity\User;
use App\EventListener\EntityListener\Abstracts\AbstractMessengerListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: AbstractArticle::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: AbstractArticle::class)]
class ArticleListener extends AbstractMessengerListener
{
    public function postPersist(AbstractArticle $article, PostPersistEventArgs $args): void
    {
        if (!empty($article->getDescription())) {
            $this->sendDiscordMessage(ArticleDiscordMessage::POST, $article);
        }
    }

    public function preUpdate(AbstractArticle $article, PreUpdateEventArgs $args): void
    {
        if (!$args->hasChangedField('description')) return;
        $prevDescr = $args->getOldValue('description');

        if ($this->isUpdateSufficient($prevDescr, $article->getDescription())) {
            $this->sendDiscordMessage(ArticleDiscordMessage::PUT, $article);
        }
    }

    protected function sendDiscordMessage(string $method, AbstractArticle $article): void
    {
        if ($this->request == null) return;
        /** @var User $user */
        $user = $this->security->getUser();
        $message = new ArticleDiscordMessage($this->request->getSchemeAndHttpHost(), $method, $user, $article);
        $this->sendChatMessage($message);
    }
}
