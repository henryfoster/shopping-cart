<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ValidationFailedExceptionListener
{
    #[AsEventListener]
    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $pathInfo = $event->getRequest()->getPathInfo();
        $previous = $exception->getPrevious();

        if ($exception instanceof ValidationFailedException) {
            $previous = $exception;
        }

        $violationsArray = [];
        if ($previous instanceof ValidationFailedException && \str_starts_with($pathInfo, '/api/')) {
            $violations = $previous->getViolations();
            foreach ($violations as $violation) {
                $violationsArray[] = [
                    'property' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }
        }

        if (!empty($violations)) {
            $response = new JsonResponse(
                data: [
                    'violations' => $violationsArray,
                ],
                status: 422,
                headers: [
                    'Content-Type' => 'application/json',
                ],
            );

            $event->setResponse($response);
        }
    }
}
