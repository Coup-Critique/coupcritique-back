<?php

namespace App\EventListener\EntityListener\Abstracts;

use App\DTO\Message\AbstractMessage;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;

abstract class AbstractMessengerListener
{
    final public const STR_LEN_COMPARISON = 100;
    protected ?Request $request;

    public function __construct(
        protected Security $security,
        protected ChatterInterface $chatter,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::preUpdate
        ];
    }

    // abstract public function postPersist(): void;

    // abstract public function preUpdate(): void;

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
