<?php
declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\EventListener;

use App\Modules\Shared\Domain\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only act if we have an API request (should always be the case).
        if ('application/json' === $request->headers->get('Content-Type')) {
            if ($exception instanceof ValidationException) {
                // Validation exception.
                $response = new JsonResponse([
                    'message' => $exception->getMessage(),
                    'errors' => $exception->getErrors(),
                    'code' => $exception->getCode(),
                    // For validation exception traces are not needed for development.
                    // 'traces' => $exception->getTrace(),
                ]);
            } else {
                // All other exceptions.
                // Customize your response object to display the exception details.
                $response = new JsonResponse([
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'traces' => $exception->getTrace(),
                ]);
            }

            // HttpExceptionInterface is a special type of exception that
            // holds status code and header details.
            if ($exception instanceof HttpExceptionInterface) {
                $response->setStatusCode($exception->getStatusCode());
                $response->headers->replace($exception->getHeaders());
            } else {
                $response->setStatusCode($exception->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Send the modified response object to the event.
            $event->setResponse($response);
        }
    }
}
