<?php

declare(strict_types=1);

namespace App\Tests\Dummy;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenInterfaceImplementation implements TokenInterface
{
    private UserInterface $user;

    public function __toString()
    {
        return '';
    }

    public function __serialize(): array
    {
        return [];
    }

    public function __unserialize(array $data): void
    {
    }

    public function getUserIdentifier()
    {
    }

    public function getRoleNames(): array
    {
        return ['ROLE_USER'];
    }

    public function getCredentials()
    {
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function isAuthenticated()
    {
        return null !== $this->user;
    }

    public function setAuthenticated(bool $isAuthenticated)
    {
    }

    public function eraseCredentials()
    {
    }

    public function getAttributes()
    {
        return [];
    }

    public function setAttributes(array $attributes)
    {
    }

    public function hasAttribute(string $name)
    {
        return true;
    }

    public function getAttribute(string $name)
    {
    }

    public function setAttribute(string $name, $value)
    {
    }

    public function getUsername()
    {
        return $this->user->getUserIdentifier();
    }

    public function serialize()
    {
        return null;
    }

    public function unserialize($data)
    {
    }
}
