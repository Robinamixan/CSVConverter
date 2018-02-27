<?php

namespace App\Service\FileReaderToBD\ControllersReading;

use App\Service\ArrayToEntitySaver\ArrayToEntitySaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductSaver;
use App\Service\ArrayToEntitySaver\EntitySavers\ProductTestSaver;
use App\Service\ArrayToEntitySaver\IEntitySaver;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityValidator\ArrayToEntityValidators\ArrayToProductValidator;
use App\Service\EntityValidator\EntityValidator;
use App\Service\EntityValidator\IArrayToEntityValidator;
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
    protected $itemsBuffer;
    protected $fileReadingReport;
    protected const BUFFER_SIZE = 5;

    public function __construct(
        FileReader $fileReader,
        ArrayToEntitySaver $arrayToEntitySaver,
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        EntityValidator $entityValidator,
        ValidatorInterface $validator,
        bool $flagTestMode
    ) {
        $this->fileReadingReport = [
            'failedRecords' => [],
            'amountFailedItems' => 0,
            'amountProcessedItems' => 0,
            'amountSuccessesItems' => 0,
        ];
        $this->validator = $validator;
        $this->entityConverter = $entityConverter;
        $this->entityValidator = $entityValidator;
        $this->entityManager = $entityManager;
        $this->arrayToEntitySaver = $arrayToEntitySaver;
        $this->fileReader = $fileReader;
        $this->flagTestMode = $flagTestMode;
        $this->itemsBuffer = [];
    }

    /**
     * @param \SplFileObject $file
     * @return array
     */
    public function readFileToBD(\SplFileObject $file): array
    {
        $productSaver = !$this->flagTestMode
            ? new ProductSaver($this->entityManager, $this->entityConverter, $this->validator)
            : new ProductTestSaver($this->entityManager, $this->entityConverter, $this->validator);

        $productValidator = new ArrayToProductValidator($this->entityConverter, $this->validator);

        $this->fileReader->setFileForRead($file);
        while ($item = $this->fileReader->getNextItem()) {
            $this->fileReadingReport['amountProcessedItems']++;
            $this->checkIsValidItemsAndSave($item, $productValidator, $productSaver);
        }

        if (!empty($this->itemsBuffer)) {
            $this->saveBufferInBD($productSaver);
        }

        return $this->fileReadingReport;
    }

    /**
     * @param array $item
     * @param IArrayToEntityValidator $productValidator
     * @param IEntitySaver $productSaver
     */
    public function checkIsValidItemsAndSave(
        array $item,
        IArrayToEntityValidator $productValidator,
        IEntitySaver $productSaver
    ): void {
        if ($this->entityValidator->isValidItemToEntityRules($item, $productValidator)) {
            $this->collectItemsAndSave($item, $productSaver);
        } else {
            $this->fileReadingReport['failedRecords'][] = $item;
            $this->fileReadingReport['amountFailedItems']++;
        }
    }

    /**
     * @param array $item
     * @param IEntitySaver $productSaver
     */
    public function collectItemsAndSave(array $item, IEntitySaver $productSaver): void
    {
        $this->itemsBuffer[] = $item;

        if (count($this->itemsBuffer) === $this::BUFFER_SIZE) {
            $this->saveBufferInBD($productSaver);
            $this->itemsBuffer = [];
        }
    }

    /**
     * @param IEntitySaver $productSaver
     */
    public function saveBufferInBD(IEntitySaver $productSaver): void
    {
        $this->arrayToEntitySaver->saveItemsArrayIntoEntity($this->itemsBuffer, $productSaver);

        $this->fileReadingReport['failedRecords'] = array_merge(
            $this->fileReadingReport['failedRecords'],
            $this->arrayToEntitySaver->getFailedRecords()
        );

        $this->fileReadingReport['amountFailedItems'] += $this->arrayToEntitySaver->getAmountFailedInserts();
        $this->fileReadingReport['amountSuccessesItems'] += $this->arrayToEntitySaver->getAmountSuccessfulInserts();
    }
}
