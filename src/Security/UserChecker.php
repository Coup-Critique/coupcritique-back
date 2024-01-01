<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    private readonly Request $request;

    public function __construct(
        RequestStack $requestStack,
        private readonly EntityManagerInterface $em
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }


    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) return;

        if ($user->getDeleted()) {
            throw new CustomUserMessageAuthenticationException('Ce compte a été supprimé.');
        }

        $this->saveIp($user);

        if ($user->getBanned()) {
            throw new CustomUserMessageAuthenticationException('Ce compte est banni.');
        }

        // if (!$user->getActivated()) {
        //     throw new CustomUserMessageAuthenticationException(
        //         'Ce compte n\'a pas été activé.<br /> 
        //         Veuillez vérifier vos mails pour y trouver le mail d\'activation de votre compte.<br /> 
        //         Il est possible qu\'il se trouve dans les spams.'
        //     );
        // }
    }

    private function saveIp(User $user): void
    {
        $ip = $this->request->getClientIp();
        $user->addIp($ip);
        $this->em->flush();
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
