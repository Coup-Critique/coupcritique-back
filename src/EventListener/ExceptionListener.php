<?php

namespace App\EventListener;

use App\Http\ApiResponse;
use App\Kernel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ExceptionListener
{
    public function __construct(
        private readonly Kernel $kernel,
        private readonly LoggerInterface $logger
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        /**
         * Don't handle exception response if we're in dev.
         * We must let the standard Symfony behaviour to do it,
         * through the error page.
         */
        if ($this->kernel->getEnvironment() !== 'prod') {
            return null;
        }

        if (!$this->isApiRequest($request)) {
            return null;
        }

        $infos = [$request->getRequestUri(), $this->getStatusCode($exception), $exception->getMessage()];

        if ($this->kernel->isDebug()) {
            $infos[] = $exception->getTraceAsString();
        }

        $this->logger->error(implode(" : ", $infos));

        $response = $this->createApiResponse($exception);
        $event->setResponse($response);
    }

    private function isApiRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api/');
    }

    private function getStatusCode(Throwable $exception): int
    {
        return $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function createApiResponse(Throwable $exception): ApiResponse
    {
        return new ApiResponse('Erreur de communication avec le serveur', null, [], $this->getStatusCode($exception));
    }
}
