<?php

namespace App\Service\ArrayToEntitySaver\EntitySavers;

use App\Entity\Product;
use App\Service\EntityConverter\ArrayToEntityConverters\ArrayToProductConverter;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityConverter\EntityToArrayConverters\ProductToArrayConverter;
use App\Service\ArrayToEntitySaver\IEntitySaver;
use App\Service\EntityConverter\IArrayToEntityConverter;
use App\Service\EntityConverter\IEntityToArrayConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class ProductSaver implements IEntitySaver
{
    protected $entityManager;
    protected $entityRepository;
    protected $entityConverter;
    protected $arrayToProductConverter;
    protected $productToArrayConverter;
    protected $failedRecords;
    protected $validRecords;
    protected $amountSuccessfulInserts;
    protected $amountFailedInserts;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        IEntityToArrayConverter $productToArrayConverter,
        IArrayToEntityConverter $arrayToProductConverter
    ) {
        $this->validRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountSuccessfulInserts = 0;
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(Product::class);
        $this->entityConverter = $entityConverter;
        $this->arrayToProductConverter = $arrayToProductConverter;
        $this->productToArrayConverter = $productToArrayConverter;
    }

    public function saveItemsArrayIntoEntity(array $items): void
    {
        $this->validRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountSuccessfulInserts = 0;
        $this->checkValidRecordsFromItems($items);
        $this->removeRepeatedRecordsByCode();
        $this->insertIntoBD();
        $items = null;
    }

    public function getFailedRecords(): array
    {
        $failedRecords = $this->failedRecords;
        $this->failedRecords = null;

        return $failedRecords;
    }

    public function getAmountFailedInserts(): int
    {
        $amountFailedInserts = $this->amountFailedInserts;
        $this->amountFailedInserts = null;

        return $amountFailedInserts;
    }

    public function getAmountSuccessfulInserts(): int
    {
        $amountSuccessfulInserts = $this->amountSuccessfulInserts;
        $this->amountSuccessfulInserts = null;

        return $amountSuccessfulInserts;
    }

    protected function checkValidRecordsFromItems(array $items): void
    {
        $validatorBuild = new ValidatorBuilder();
        $validator = $validatorBuild
            ->enableAnnotationMapping()
            ->getValidator();

        foreach ($items as $item) {
            $record = $this->entityConverter->convertArrayToEntity(
                $item,
                $this->arrayToProductConverter
            );
            if ($this->isValidRecord($record, $validator)) {
                $this->amountSuccessfulInserts++;
                $this->validRecords[] = $record;
            } else {
                $this->addFailedRecord($record);
            }
            $record = null;
            $item = null;
        }
        $items = null;
        $validator = null;
    }

    protected function insertIntoBD(): void
    {
        foreach ($this->validRecords as $validRecord) {
            $this->entityManager->persist($validRecord);
        }

        try {
            $this->entityManager->flush();
            $this->entityManager->clear();
        } catch (\Doctrine\DBAL\DBALException $e) {
            $e = null;
            $this->reOpenEntityManager();
            foreach ($this->validRecords as $validRecord) {
                if ($this->isInBD($validRecord)) {
                    $this->entityManager->detach($validRecord);
                    $this->addFailedRecord($validRecord);
                    $this->amountSuccessfulInserts--;
                } else {
                    $this->entityManager->persist($validRecord);
                }
                $validRecord = null;
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
        $this->validRecords = null;
    }

    protected function reOpenEntityManager(): void
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
            $this->entityManager->clear();
        }
    }

    protected function isValidRecord(Product $record, ValidatorInterface $validator): bool
    {
        $errors = $validator->validate($record);
        $validatorBuild = null;
        $record = null;

        return count($errors) === 0;
    }

    protected function removeRepeatedRecordsByCode(): void
    {
        $productCodesColumn = [];
        foreach ($this->validRecords as $record) {
            $productCodesColumn[] = $record->getProductCode();
            $record = null;
        }
        $uniqueProductCodesColumn = array_unique($productCodesColumn);

        foreach ($this->validRecords as $key => $record) {
            if (!array_key_exists($key, $uniqueProductCodesColumn)) {
                $this->addFailedRecord($record);
                $this->amountSuccessfulInserts--;

                $this->validRecords[$key] = null;
                sort($this->validRecords);
            }
            $key = null;
            $record = null;
        }
    }

    protected function addFailedRecord(Product $record): void
    {
        $this->failedRecords[] = $this->entityConverter->convertEntityToArray(
            $record,
            $this->productToArrayConverter
        );
        $this->amountFailedInserts++;
        $record = null;
    }

    protected function isInBD(Product $item): bool
    {
        $productCode = $item->getProductCode();
        $item = null;

        return $this->entityRepository->productCodeExists($productCode);
    }

    public function getCurrentMemorySize()
    {
        return (int)(memory_get_usage() / 1024).' KB';
    }
}
