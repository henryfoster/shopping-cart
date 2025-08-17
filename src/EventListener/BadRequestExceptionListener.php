<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class BadRequestExceptionListener
{
    public const string MESSAGE = 'Bad request. Invalid json.';

    #[AsEventListener]
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $pathInfo = $event->getRequest()->getPathInfo();

        if (str_starts_with($pathInfo, '/api/') && $exception instanceof BadRequestHttpException) {
            $response = new JsonResponse(
                data: [
                    'error' => self::MESSAGE,
                ],
                status: Response::HTTP_BAD_REQUEST,
                headers: [
                    'Content-Type' => 'application/json',
                ]
            );
            $event->setResponse($response);
        }
    }
}
