<?php

namespace App\Controller;


use App\Entity\File;
use App\Form\FilesLoadForm;
use App\Service\Reader\Reader;
use App\Service\Saver\Saver;
use App\Service\Saver\Savers\TblProductDataSaver;
use App\Service\Saver\Savers\TblProductDataTestSaver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends Controller
{
    /**
     * Matches / exactly
     *
     * @Route("/", name="main_page")
     *
     * @param Request $request
     * @param Reader $reader
     * @param Saver $saver
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function fileLoadAction(Request $request, Reader $reader, Saver $saver, EntityManagerInterface $entityManager)
    {
        $loading_file = new File();

        $form = $this->createForm(FilesLoadForm::class, $loading_file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $loading_file->getFile();
            $contain = $reader->loadFile($file);

            if ($contain) {

                if (!$loading_file->getFlagTestMode()) {
                    $saver->saveArrayIntoEntity($contain, new TblProductDataSaver($entityManager));
                } else {
                    $saver->saveArrayIntoEntity($contain, new TblProductDataTestSaver($entityManager));
                }

                return $this->render('FileParser/main.html.twig', array(
                    'form' => $form->createView(),
                    'loadReport' => null,
                    'failedRecords' => $saver->getFailedRecords(),
                    'amountFailed' => $saver->getAmountFailedInserts(),
                    'amountProcessed' => $saver->getAmountProcessedRecords(),
                    'amountSuccesses' => $saver->getAmountSuccessfulRecords(),
                ));

            } else {
                return $this->render('FileParser/main.html.twig', array(
                    'form' => $form->createView(),
                    'loadReport' => $reader->getFailReport(),
                    'amountFails' => null,
                    'failedRecords' => null,
                    'amountProcessed' => null,
                    'amountSuccesses' => null,
                ));
            }
        }

        return $this->render('FileParser/main.html.twig', array(
            'form' => $form->createView(),
            'loadReport' => null,
            'amountFails' => null,
            'failedRecords' => null,
            'amountProcessed' => null,
            'amountSuccesses' => null,
        ));
    }
}
