<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ErrorController extends AbstractController
{
    public function show(Throwable $exception): Response
    {
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
        }

        return $this->render('bundles/TwigBundle/Exception/error404.html.twig', [
            'status_code' => $statusCode,
            'status_text' => Response::$statusTexts[$statusCode] ?? 'Unknown Error',
        ], new Response('', $statusCode));
    }
}
