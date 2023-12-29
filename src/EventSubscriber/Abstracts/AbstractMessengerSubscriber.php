<?php

namespace App\EventSubscriber\Abstracts;

use App\DTO\Message\AbstractMessage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Security;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

abstract class AbstractMessengerSubscriber implements EventSubscriberInterface
{
    public const STR_LEN_COMPARISON = 100;

    protected ChatterInterface $chatter;
    protected Security $security;
    protected ?Request $request;

    public function __construct(Security $security, ChatterInterface $chatter, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->chatter = $chatter;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::preUpdate
        ];
    }

    /** @return mixed */
    abstract protected function getEntity(LifecycleEventArgs $args);

    abstract public function postPersist(LifecycleEventArgs $args): void;

    abstract public function preUpdate(PreUpdateEventArgs $args): void;

    protected function sendChatMessage(AbstractMessage $message): void
    {
        $chat = new ChatMessage('');
        // Add the custom options to the chat message and send the message
        $chat->options($message->getOptions());
        $this->chatter->send($chat);
    }

    protected function isUpdateSufficient(string $prevValue, string $value): bool
    {
        return !empty($value) && (empty($prevValue)
            || abs(strlen($prevValue) - strlen($value)) > self::STR_LEN_COMPARISON
        );
    }
}
