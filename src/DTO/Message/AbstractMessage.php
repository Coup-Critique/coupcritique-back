<?php

namespace App\DTO\Message;

use Symfony\Component\Notifier\Message\MessageOptionsInterface;

abstract class AbstractMessage
{
    protected MessageOptionsInterface $options;

    public function getOptions(): MessageOptionsInterface
    {
        return $this->options;
    }

    abstract public function getSubject(): string;

    abstract public function getContent(): string;
}
