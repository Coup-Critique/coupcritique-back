<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @author ii02735
 * @package App\EventListener
 * Class created to allow jwt authentication flow.
 * 
 * Normally, when an authentication is made through JWT (bearer)
 * the authorization header must be used, but it can conflicts when
 * it is already used for something else (htpasswd : basic authentication)
 * 
 */

class AuthorizeListener
{

    /**
     * In order to use both basic auth and jwt auth
     * we decide to provide the token in a custom header
     * named "jwtauthorization".
     * 
     * When the request is intercepted by Symfony
     * we tell to override the authorization header
     * by the jwtauthorization one (SF auth system only works 
     * with the classic authorization header and not a custom header)
     * 
     * @param RequestEvent $event the request sent by the http client (browser / react, postman...)
     * @return void
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        /** @var HeaderBag $headers */
        $headers = $event->getRequest()->headers;
        if ($headers->has("jwtauthorization")) {
            $headers->set("authorization", $headers->get("jwtauthorization"));
        }
    }
}
