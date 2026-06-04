<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Exception\AccessDeniedException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\UnauthorizedException;
use App\Domain\Exception\ValidationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException as SecurityAccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException as SecurityAuthenticationException;

class JsonExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = 500;
        $headers = [];
        $payload = [
            'error' => [
                'message' => $exception->getMessage(),
            ],
        ];

        if ($exception instanceof ValidationException) {
            $statusCode = 400;
            $payload['error']['errors'] = $exception->getErrors();
        } elseif ($exception instanceof UnauthorizedException) {
            $statusCode = 401;
        } elseif ($exception instanceof SecurityAuthenticationException) {
            $statusCode = 401;
        } elseif ($exception instanceof AccessDeniedException) {
            $statusCode = 403;
        } elseif ($exception instanceof SecurityAccessDeniedException) {
            $statusCode = 403;
        } elseif ($exception instanceof NotFoundException) {
            $statusCode = 404;
        } elseif ($exception instanceof DomainException) {
            $statusCode = 400;
        } elseif ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        }

        $payload['error']['statusCode'] = $statusCode;

        $response = new JsonResponse($payload, $statusCode, $headers);

        $event->setResponse($response);
    }
}
