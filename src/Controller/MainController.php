<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FilesLoadForm;
use App\Service\EntityConverter\EntityConverter;
use App\Service\FileReader\FileReader;
use App\Service\ArrayToEntitySaver\ArrayToEntitySaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductDataSaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductDataTestSaver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function fileLoadAction(
        Request $request,
        FileReader $fileReader,
        ArrayToEntitySaver $arrayToEntitySaver,
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        ValidatorInterface $validator
    ) {
        $loadingFile = new File();

        $templateArgs = [
            'form' => null,
            'loadReport' => null,
            'amountFailed' => null,
            'failedRecords' => null,
            'amountProcessed' => null,
            'amountSuccesses' => null,
        ];

        $form = $this->createForm(FilesLoadForm::class, $loadingFile);
        $form->handleRequest($request);

        $templateArgs['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $loadingFile->getFile();

            $fileContain = $fileReader->loadFileToArray($file);

            if ($fileContain) {
                if (!$loadingFile->getFlagTestMode()) {
                    $arrayToEntitySaver->saveArrayIntoEntity(
                        $fileContain,
                        new ProductDataSaver($entityManager, $entityConverter, $validator)
                    );
                } else {
                    $arrayToEntitySaver->saveArrayIntoEntity(
                        $fileContain,
                        new ProductDataTestSaver($entityManager, $entityConverter, $validator)
                    );
                }

                $templateArgs['failedRecords'] = $arrayToEntitySaver->getFailedRecords();
                $templateArgs['amountFailed'] = $arrayToEntitySaver->getAmountFailedInserts();
                $templateArgs['amountProcessed'] = $arrayToEntitySaver->getAmountProcessedRecords();
                $templateArgs['amountSuccesses'] = $arrayToEntitySaver->getAmountSuccessfulRecords();
            }
        }

        return $this->render(
            'FileParser/main.html.twig',
            [
                'form' => $templateArgs['form'],
                'loadReport' => $templateArgs['loadReport'],
                'amountFailed' => $templateArgs['amountFailed'],
                'failedRecords' => $templateArgs['failedRecords'],
                'amountProcessed' => $templateArgs['amountProcessed'],
                'amountSuccesses' => $templateArgs['amountSuccesses'],
            ]
        );
    }
}
