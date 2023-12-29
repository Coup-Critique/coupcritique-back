<?php

namespace App\DTO\Message;

use App\Entity\User;
use Symfony\Component\Notifier\Bridge\Discord\DiscordOptions;
use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordEmbed;
use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordFieldEmbedObject;
use Symfony\Component\Notifier\Bridge\Discord\Embeds\DiscordFooterEmbedObject;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

abstract class AbstractDiscordMessage extends AbstractMessage
{
    public const POST = 'POST';
    public const PUT = 'PUT';
    public const MAX_MSG_LEN = 300;

    protected string $domain;
    protected string $method;
    protected User $user;

    /** @var DiscordOptions $options */
    protected MessageOptionsInterface $options;

    public function __construct(string $domain, string $method, User $user)
    {
        $this->domain = $domain;
        $this->method = $method;
        $this->user = $user;

        $this->options = new DiscordOptions();
        $this->fillOptions();
    }

    protected function getAction(): string
    {
        return $this->method === self::POST ? "Création" : "Mise à jour";
    }

    protected function fillOptions(): void
    {
        $this->options->addEmbed((new DiscordEmbed())
                ->title(
                    $this->getSubject() . "\r\n"
                        . 'Auteur : ' . $this->getAuthor()
                )
                ->addField((new DiscordFieldEmbedObject())
                        ->name('URL')
                        ->value($this->getUrl())
                        ->inline(true)
                )
                ->footer((new DiscordFooterEmbedObject())
                        ->text($this->getContent())
                )
        );
    }

    public function getAuthor(): string
    {
        return $this->user->getUsername()
            . ($this->user->getDiscordName() ? " (Discord : " . $this->user->getDiscordName() . ')' : ''
            );
    }

    abstract public function getUrl(): string;

    protected function limitContent(string $content): string
    {
        if (strlen($content) > self::MAX_MSG_LEN) {
            $content = substr($content, 0, self::MAX_MSG_LEN - 3) . '...';
        }
        return $content;
    }
}
