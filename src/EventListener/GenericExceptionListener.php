<?php

namespace App\EventListener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GenericExceptionListener
{
    public const string MESSAGE = 'Internal Server Error';

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    #[AsEventListener(priority: -100)]
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $pathInfo = $event->getRequest()->getPathInfo();

        $this->logger->error($exception->getMessage(), [
            'exception' => $exception,
        ]);

        if (str_starts_with($pathInfo, '/api/') && !$event->hasResponse()) {
            $response = new JsonResponse(
                data: [
                    'error' => self::MESSAGE,
                ],
                status: Response::HTTP_INTERNAL_SERVER_ERROR,
                headers: [
                    'Content-Type' => 'application/json',
                ]
            );
            $event->setResponse($response);
        }
    }
}
