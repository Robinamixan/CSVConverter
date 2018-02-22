<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 21.2.18
 * Time: 14.54
 */

namespace App\Controller;

use App\Entity\File;
use App\Form\FilesLoadForm;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;
use Twig\Environment;

class ExceptionController extends BaseExceptionController
{
    private $builder;

    public function __construct(Environment $twig, FormFactoryInterface $builder)
    {
        $this->builder = $builder;
        parent::__construct($twig, true);
    }

    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));
        $showException = $request->attributes->get('showException', $this->debug);
        $code = $exception->getStatusCode();

        $loadingFile = new File();
        $form = $this->builder->create(FilesLoadForm::class, $loadingFile);
        $form->handleRequest($request);

        if ($exception->getMessage() !== "Unsupported type of input file") {
            return new Response(
                $this->twig->render(
                    (string)$this->findTemplate(request, $request->getRequestFormat(), $code, $showException),
                    [
                        'status_code' => $code,
                        'status_text' => isset(Response::$statusTexts[$code]) ? Response::$statusTexts[$code] : '',
                        'exception' => $exception,
                        'logger' => $logger,
                        'currentContent' => $currentContent,
                    ]
                ), 200, ['Content-Type' => $request->getMimeType($request->getRequestFormat()) ?: 'text/html']
            );
        } else {
            return new Response(
                $this->twig->render(
                    'FileParser/main.html.twig',
                    [
                        'form' => $form->createView(),
                        'loadReport' => $exception->getMessage(),
                        'amountFailed' => null,
                        'failedRecords' => null,
                        'amountProcessed' => null,
                        'amountSuccesses' => null,
                    ]
                ),
                200,
                ['Content-Type' => $request->getMimeType($request->getRequestFormat()) ?: 'text/html']
            );
        }
    }
}
