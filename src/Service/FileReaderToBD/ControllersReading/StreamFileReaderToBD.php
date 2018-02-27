<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 27.2.18
 * Time: 12.45
 */

namespace App\Service\FileReaderToBD\ControllersReading;

use App\Service\ArrayToEntitySaver\ArrayToEntitySaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductSaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductTestSaver;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityValidator\ArrayToEntityValidators\ArrayToProductValidator;
use App\Service\EntityValidator\EntityValidator;
use App\Service\FileReader\FileReader;
use App\Service\FileReaderToBD\IControllerReading;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StreamFileReaderToBD implements IControllerReading
{
    protected $fileReader;
    protected $arrayToEntitySaver;
    protected $entityManager;
    protected $entityConverter;
    protected $entityValidator;
    protected $validator;
    protected $flagTestMode;

    public function __construct(
        FileReader $fileReader,
        ArrayToEntitySaver $arrayToEntitySaver,
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        EntityValidator $entityValidator,
        ValidatorInterface $validator,
        bool $flagTestMode
    ) {
        $this->validator = $validator;
        $this->entityConverter = $entityConverter;
        $this->entityValidator = $entityValidator;
        $this->entityManager = $entityManager;
        $this->arrayToEntitySaver = $arrayToEntitySaver;
        $this->fileReader = $fileReader;
        $this->flagTestMode = $flagTestMode;
    }

    /**
     * @param \SplFileObject $file
     * @return array
     */
    public function readFileToBD(\SplFileObject $file): array
    {
        $fileReadingReport = [
            'failedRecords' => [],
            'amountFailedItems' => 0,
            'amountProcessedItems' => 0,
            'amountSuccessesItems' => 0,
        ];

        $productSaver = !$this->flagTestMode
            ? new ProductSaver($this->entityManager, $this->entityConverter, $this->validator)
            : new ProductTestSaver($this->entityManager, $this->entityConverter, $this->validator);

        $productValidator = new ArrayToProductValidator($this->entityConverter, $this->validator);

        $this->fileReader->setFileForRead($file);
        while ($item = $this->fileReader->getNextItem()) {
            $fileReadingReport['amountProcessedItems']++;

            if ($this->entityValidator->isValidItemToEntityRules($item, $productValidator)) {
                $itemsBuffer[] = $item;

                if (count($itemsBuffer) === 5) {
                    $fileReadingReport = $this->saveBufferInBD($itemsBuffer, $productSaver, $fileReadingReport);
                    $itemsBuffer = [];
                }
            } else {
                $fileReadingReport['failedRecords'][] = $item;
                $fileReadingReport['amountFailedItems']++;
            }
        }

        if (!empty($itemsBuffer)) {
            $fileReadingReport = $this->saveBufferInBD($itemsBuffer, $productSaver, $fileReadingReport);
        }

        return $fileReadingReport;
    }

    /**
     * @param $itemsBuffer
     * @param $productSaver
     * @param $fileReadingReport
     * @return mixed
     */
    public function saveBufferInBD($itemsBuffer, $productSaver, $fileReadingReport): array
    {
        $this->arrayToEntitySaver->saveItemsArrayIntoEntity($itemsBuffer, $productSaver);

        $fileReadingReport['failedRecords'] = array_merge(
            $fileReadingReport['failedRecords'],
            $this->arrayToEntitySaver->getFailedRecords()
        );

        $fileReadingReport['amountFailedItems'] += $this->arrayToEntitySaver->getAmountFailedInserts();
        $fileReadingReport['amountSuccessesItems'] += $this->arrayToEntitySaver->getAmountSuccessfulInserts();

        return $fileReadingReport;
    }
}
