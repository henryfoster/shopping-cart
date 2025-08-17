<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotFoundExceptionListener
{
    #[AsEventListener]
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $pathInfo = $event->getRequest()->getPathInfo();

        if (str_starts_with($pathInfo, '/api/') && $exception instanceof NotFoundHttpException) {
            $response = new JsonResponse(
                data: [
                    'error' => 'Not found '.$pathInfo,
                ],
                status: Response::HTTP_NOT_FOUND,
                headers: [
                    'Content-Type' => 'application/json',
                ]
            );
            $event->setResponse($response);
        }
    }
}
