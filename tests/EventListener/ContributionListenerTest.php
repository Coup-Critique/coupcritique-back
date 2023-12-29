<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use App\Entity\ContributionLog;
use App\Entity\User;
use App\EventListener\ContributionListener;
use App\Tests\DatabaseDependantTestCase;
use App\Tests\Dummy\ContributeControllerImplementation;
use App\Tests\Dummy\SimpleController;
use App\Tests\Dummy\TokenInterfaceImplementation;
use Hautelook\AliceBundle\PhpUnit\RecreateDatabaseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ContributionListenerTest extends DatabaseDependantTestCase
{
    use RecreateDatabaseTrait;

    /**
     * @group Unit
     */
    public function testContributeAttributeIsAdded(): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $request = Request::create('/dummy', 'POST');

        $event = new ControllerEvent(
            static::$kernel,
            [new ContributeControllerImplementation(), 'action'],
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener = new ContributionListener($tokenStorage, $this->entityManager);
        $listener->onKernelController($event);

        $this->assertArrayHasKey('contribute', $request->attributes->all());
    }

    /**
     * @group Unit
     * @dataProvider invalidCasesProvider
     */
    public function testContributeAttributeIsNotAdded(string $method, callable $controller): void
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $request = Request::create('/dummy', $method);

        $event = new ControllerEvent(
            static::$kernel,
            $controller,
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $listener = new ContributionListener($tokenStorage, $this->entityManager);
        $listener->onKernelController($event);

        $this->assertArrayNotHasKey('contribute', $request->attributes->all());
    }

    public function invalidCasesProvider(): \Generator
    {
        yield 'Bad method' => [
            'method' => 'GET',
            'controller' => [new ContributeControllerImplementation(), 'action'],
        ];

        yield 'Bad controller' => [
            'method' => 'POST',
            'controller' => [new SimpleController(), 'action'],
        // Should implement ContributeControllerInterface
        ];
    }

    /**
     * @depends testContributeAttributeMustBeAdded
     * @group Unit
     */
    public function testLogIsSaved(): void
    {
        $dummyUser = new User();
        $dummyUser->setUsername('dummy user');
        $dummyToken = new TokenInterfaceImplementation();
        $dummyToken->setUser($dummyUser);

        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($dummyToken);

        $request = Request::create('/dummy', 'POST', [], [], [], [], '{ "test": "body" }');
        $request->headers->set('Content-Type', 'application/json');
        $request->attributes->add(['contribute' => true]);

        $response = new Response('{ "response": "OK" }');

        $event = new ResponseEvent(
            static::$kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $listener = new ContributionListener($tokenStorage, $this->entityManager);
        $listener->onKernelResponse($event);

        $this->assertNotNull(
            $this->entityManager->getRepository(ContributionLog::class)
                ->findOneBy(['url' => 'http://localhost/dummy'])
        );
    }
}
