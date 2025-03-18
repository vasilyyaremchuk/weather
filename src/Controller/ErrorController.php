<?php

/**
 * This file is part of the Weather Application.
 */

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Controller for handling error pages and logging error information.
 */
class ErrorController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param LoggerInterface $logger Logger for error recording
     */
    public function __construct(private LoggerInterface $logger)
    {
    }

    /**
     * Show error page based on the exception.
     *
     * @param Throwable $exception The exception that triggered the error
     *
     * @return Response The error page response
     */
    public function show(Throwable $exception): Response
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        }

        $this->logger->error('Error page accessed', [
            'status_code' => $statusCode,
            'error_message' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Unknown Error',
        ], new Response('', $statusCode));
    }
}
