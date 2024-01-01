<?php

namespace App\EventListener;

use App\Controller\Api\ContributeControllerInterface;
use App\Entity\ContributionLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContributionListener
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorageInterface,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if ($event->getRequest()->getMethod() === 'GET') return;
        $controller = $event->getController();

        if (is_array($controller))
            $controller = $controller[0];

        if ($controller instanceof ContributeControllerInterface) {
            $event->getRequest()->attributes->add(['contribute' => true]);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (
            $event->getRequest()->getContentTypeFormat() != 'application/json'
            && $event->getRequest()->getContentTypeFormat() != 'json'
        ) {
            return;
        }
        if ($event->getRequest()->getMethod() === 'GET') return;

        /**
         * If the method is not GET, there is a strong possibility
         * that the user is authenticated, especially when the resource
         * can be contributed
         */
        if ($this->tokenStorageInterface->getToken() === null) return;

        if (!$event->getRequest()->attributes->has('contribute'))
            return;

        $user = $this->tokenStorageInterface->getToken()->getUser();
        $request = $event->getRequest();
        $response = $event->getResponse();
        $contributionLog = new ContributionLog;

        if ($event->getRequest()->getMethod() !== 'DELETE') {
            $contributionLog->setRequestBody($request->toArray());
        }
        $contributionLog->setResponseBody(json_decode($response->getContent(), true));
        $contributionLog->setHttpResponseCode($response->getStatusCode());
        $contributionLog->setHttpMethod($event->getRequest()->getMethod());
        $contributionLog->setUrl($event->getRequest()->getUri());
        $contributionLog->setUsername($user->getUserIdentifier());

        $this->entityManager->persist($contributionLog);
        $this->entityManager->flush();
    }
}
