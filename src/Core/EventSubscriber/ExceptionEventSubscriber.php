<?php

namespace App\Core\EventSubscriber;

use App\Core\Exception\ApiException;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                'onException', 4096
            ]
        ];
    }

    public function onException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof ApiException) {
            return;
        }

        $event->setResponse(new JsonResponse($exception, $exception->getCode()));
    }
}