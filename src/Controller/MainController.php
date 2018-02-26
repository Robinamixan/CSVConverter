<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FilesLoadForm;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityValidator\ArrayToEntityValidators\ArrayToProductValidator;
use App\Service\EntityValidator\EntityValidator;
use App\Service\FileReader\FileReader;
use App\Service\ArrayToEntitySaver\ArrayToEntitySaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductSaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductTestSaver;
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
     * @param EntityValidator $entityValidator
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function fileLoadAction(
        Request $request,
        FileReader $fileReader,
        ArrayToEntitySaver $arrayToEntitySaver,
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        EntityValidator $entityValidator,
        ValidatorInterface $validator
    ) {
        $loadingFile = new File();

        $templateArgs = [
            'form' => null,
            'loadReport' => null,
            'failedRecords' => [],
            'amountFailed' => 0,
            'amountProcessed' => 0,
            'amountSuccesses' => 0,
        ];

        $form = $this->createForm(FilesLoadForm::class, $loadingFile);
        $form->handleRequest($request);

        $templateArgs['form'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $loadingFile->getFile();
            $productSaver = !$loadingFile->getFlagTestMode()
                ? new ProductSaver($entityManager, $entityConverter, $validator)
                : new ProductTestSaver($entityManager, $entityConverter, $validator);
            $productValidator = new ArrayToProductValidator($entityConverter, $validator);


            $fileReader->setFileForRead($file);
            while ($item = $fileReader->getNextItem()) {
                $templateArgs['amountProcessed']++;

                if ($entityValidator->isValidItemToEntityRules($item, $productValidator)){
                    $itemsBuffer[] = $item;

                    if (count($itemsBuffer) === 5) {
                        $arrayToEntitySaver->saveArrayIntoEntity($itemsBuffer, $productSaver);

                        $templateArgs['failedRecords'] = array_merge(
                            $templateArgs['failedRecords'],
                            $arrayToEntitySaver->getFailedRecords()
                        );

                        $templateArgs['amountFailed'] += $arrayToEntitySaver->getAmountFailedInserts();
                        $templateArgs['amountSuccesses'] += $arrayToEntitySaver->getAmountSuccessfulInserts();
                        $itemsBuffer = [];
                    }
                } else {
                    $templateArgs['failedRecords'][] = $item;
                    $templateArgs['amountFailed']++;
                }
            }

            if (!empty($itemsBuffer)) {
                $arrayToEntitySaver->saveArrayIntoEntity($itemsBuffer, $productSaver);
                $templateArgs['failedRecords'] = array_merge(
                    $templateArgs['failedRecords'],
                    $arrayToEntitySaver->getFailedRecords()
                );
                $templateArgs['amountFailed'] += $arrayToEntitySaver->getAmountFailedInserts();
                $templateArgs['amountSuccesses'] += $arrayToEntitySaver->getAmountSuccessfulInserts();
            }
        }

        return $this->render(
            'FileParser/main.html.twig', $templateArgs);
    }
}
