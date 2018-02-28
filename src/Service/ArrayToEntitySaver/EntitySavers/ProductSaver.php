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

class ProductSaver implements IEntitySaver
{
    protected $entityManager;
    protected $entityRepository;
    protected $entityConverter;
    protected $validator;
    protected $arrayToProductConverter;
    protected $productToArrayConverter;
    protected $failedRecords;
    protected $validRecords;
    protected $amountSuccessfulInserts;
    protected $amountFailedInserts;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        ValidatorInterface $validator,
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
        $this->validator = $validator;
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
        unset($items);
    }

    public function getFailedRecords(): array
    {
        return $this->failedRecords;
    }

    public function getAmountFailedInserts(): int
    {
        return $this->amountFailedInserts;
    }

    public function getAmountSuccessfulInserts(): int
    {
        return $this->amountSuccessfulInserts;
    }

    protected function checkValidRecordsFromItems(array $items): void
    {
        foreach ($items as $item) {
            $record = $this->entityConverter->convertArrayToEntity(
                $item,
                $this->arrayToProductConverter
            );
            if ($this->isValidRecord($record)) {
                $this->amountSuccessfulInserts++;
                $this->validRecords[] = $record;
            } else {
                $this->addFailedRecord($record);
            }
            unset($item);
        }
    }

    protected function insertIntoBD(): void
    {
        foreach ($this->validRecords as $validRecord) {

            $this->entityManager->persist($validRecord);
        }

        try {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
        catch (\Doctrine\DBAL\DBALException $e) {
            $this->reOpenEntityManager();

            foreach ($this->validRecords as $validRecord) {
                if ($this->isInBD($validRecord)) {
                    $this->entityManager->detach($validRecord);
                    $this->addFailedRecord($validRecord);
                    $this->amountSuccessfulInserts--;
                } else {
                    $this->entityManager->persist($validRecord);
                }
                unset($validRecord);
            }
            $this->entityManager->flush();
            $this->entityManager->clear();
        }
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

    protected function isValidRecord(Product $record): bool
    {
        $errors = $this->validator->validate($record);
        unset($record);
        return count($errors) === 0;
    }

    protected function removeRepeatedRecordsByCode(): void
    {
        $productCodesColumn = [];
        foreach ($this->validRecords as $record) {
            $productCodesColumn[] = $record->getProductCode();
            unset($record);
        }
        $uniqueProductCodesColumn = array_unique($productCodesColumn);

        foreach ($this->validRecords as $key => $record) {
            if (!array_key_exists($key, $uniqueProductCodesColumn)) {
                $this->addFailedRecord($record);
                $this->amountSuccessfulInserts--;

                unset($this->validRecords[$key]);
                sort($this->validRecords);
            }
            unset($key, $record);
        }
    }

    protected function addFailedRecord(Product $record): void
    {
        $this->failedRecords[] = $this->entityConverter->convertEntityToArray(
            $record,
            $this->productToArrayConverter
        );
        $this->amountFailedInserts++;
        unset($record);
    }

    protected function isInBD(Product $item): bool
    {
        $productCode = $item->getProductCode();
        unset($item);
        return $this->entityRepository->productCodeExists($productCode);
    }
}
