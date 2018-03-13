<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FilesLoadForm;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityValidator\EntityValidator;
use App\Service\FileReader\FileReader;
use App\Service\ArrayToEntitySaver\ArrayToEntitySaver;
use App\Service\FileReaderToBD\ControllersReading\StreamFileReaderToBD;
use App\Service\FileReaderToBD\FileReaderToBD;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class MainController extends Controller
{
    /**
     * Matches / exactly
     *
     * @Route("/", name="main_page")
     *
     * @param Request $request
     * @param FileReader $fileReader
     * @param ArrayToEntitySaver $arrayToEntitySaver
     * @param EntityManagerInterface $entityManager
     * @param EntityConverter $entityConverter
     * @param EntityValidator $entityValidator
     * @param ValidatorInterface $validator
     * @param FileReaderToBD $fileReaderToBD
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function fileLoadAction(
        Request $request,
        FileReader $fileReader,
        ArrayToEntitySaver $arrayToEntitySaver,
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        EntityValidator $entityValidator,
        FileReaderToBD $fileReaderToBD
    ) {
        $loadingFile = new File();
        $validatorBuilder = new ValidatorBuilder();

        $templateArgs = [
            'form' => null,
            'loadReport' => null,
            'failedRecords' => [],
            'amountFailedItems' => 0,
            'amountProcessedItems' => 0,
            'amountSuccessesItems' => 0,
            'processingTime' => 0,
            'amountMemory' => '',
        ];

        $form = $this->createForm(FilesLoadForm::class, $loadingFile);
        $form->handleRequest($request);
        $templateArgs['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $start = microtime(true);
            $streamFileReader = new StreamFileReaderToBD(
                $fileReader,
                $arrayToEntitySaver,
                $entityManager,
                $entityConverter,
                $entityValidator,
                $validatorBuilder,
                $loadingFile->getFlagTestMode()
            );

            $readingReport = $fileReaderToBD->readFileToBD($loadingFile->getFile(), $streamFileReader);

            $templateArgs['processingTime'] = microtime(true) - $start;
            $templateArgs['amountMemory'] = (int)(memory_get_peak_usage() / 1024).' KB';

            $templateArgs = array_merge($templateArgs, $readingReport);
        }

        return $this->render('FileParser/main.html.twig', $templateArgs);
    }
}
