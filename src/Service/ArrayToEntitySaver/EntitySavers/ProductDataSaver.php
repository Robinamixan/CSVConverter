<?php

namespace App\Service\ArrayToEntitySaver\EntitySavers;

use App\Entity\ProductData;
use App\Service\EntityConverter\ArrayToEntityConverters\ArrayToProductDataConverter;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityConverter\EntityToArrayConverters\ProductDataToArrayConverter;
use App\Service\ArrayToEntitySaver\IEntitySaver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDataSaver implements IEntitySaver
{
    protected $entityManager;
    protected $entityRepository;
    protected $entityConverter;
    protected $validator;
    protected $failedRecords;
    protected $validRecords;
    protected $amountProcessedRecords;
    protected $amountSuccessfulRecords;
    protected $amountFailedInserts;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        ValidatorInterface $validator
    ) {
        $this->validRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountProcessedRecords = 0;
        $this->amountSuccessfulRecords = 0;
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(ProductData::class);
        $this->entityConverter = $entityConverter;
        $this->validator = $validator;
    }

    public function saveArrayIntoEntity(array $contain): void
    {
        $this->checkContain($contain);
        $this->insertIntoBD();
    }

    public function getFailedRecords(): array
    {
        return $this->failedRecords;
    }

    public function getAmountFailedInserts(): int
    {
        return $this->amountFailedInserts;
    }

    public function getAmountSuccessfulRecords(): int
    {
        return $this->amountSuccessfulRecords;
    }

    public function getAmountProcessedRecords(): int
    {
        return $this->amountProcessedRecords;
    }

    protected function checkContain(array $contain): void
    {
        foreach ($contain as $item) {
            $this->amountProcessedRecords++;
            if ($this->isValidArray($item)) {
                $record = $this->entityConverter->convertArrayToEntity(
                    $item,
                    new ArrayToProductDataConverter()
                );
                if ($this->isValidRecord($record)) {
                    $this->amountSuccessfulRecords++;
                    $this->validRecords[] = $record;
                } else {
                    $this->amountFailedInserts++;
                    $this->failedRecords[] = $item;
                }
            } else {
                $this->amountFailedInserts++;
                $this->failedRecords[] = $item;
            }
        }
    }

    protected function insertIntoBD(): void
    {
        $step = 5;
        $lastStepNumber = -1;
        for ($recordNumber = 0; $recordNumber < count($this->validRecords); $recordNumber++) {
            $this->entityManager->persist($this->validRecords[$recordNumber]);
            if ((($recordNumber % $step == 0) and
                    ($recordNumber != 0)) or
                ($recordNumber == count($this->validRecords) - 1)) {
                try {
                    $this->entityManager->flush();
                } catch (\Doctrine\DBAL\DBALException $e) {
                    $this->reOpenEntityManager();

                    for ($lastRecordNumber = $recordNumber; $lastRecordNumber > $lastStepNumber; $lastRecordNumber--) {
                        if ($this->isInBD($this->validRecords[$lastRecordNumber])) {
                            $this->entityManager->detach($this->validRecords[$lastRecordNumber]);
                            $this->failedRecords[] = $this->entityConverter->convertEntityToArray(
                                $this->validRecords[$lastRecordNumber],
                                new ProductDataToArrayConverter()
                            );
                            $this->amountSuccessfulRecords--;
                            $this->amountFailedInserts++;
                        } else {
                            $this->entityManager->persist($this->validRecords[$lastRecordNumber]);
                        }
                    }

                    $this->entityManager->flush();
                }
                $lastStepNumber = $recordNumber;
            }
        }
    }

    protected function reOpenEntityManager(): void
    {
        if (!$this->entityManager->isOpen()) {
            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }

    protected function isValidArray(array $item): bool
    {

        if (!$this->hasNeededField($item)) {
            return false;
        }

        if (($item['product_discontinued'] !== 'yes') && (!is_null($item['product_discontinued']))) {
            if (is_null($item['product_discontinued'])) {
                return false;
            }
        }

        return true;
    }

    protected function isValidRecord(ProductData $record): bool
    {
        $errors = $this->validator->validate($record);

        return empty(count($errors));
    }

    protected function isInBD(ProductData $item): bool
    {
        return $this->entityRepository->productCodeExists($item->getProductCode());
    }

    protected function hasNeededField(array $item): bool
    {
        if (!array_key_exists('product_name', $item)) {
            return false;
        }

        if (!array_key_exists('product_code', $item)) {
            return false;
        }

        if (!array_key_exists('product_description', $item)) {
            return false;
        }

        if (!array_key_exists('product_stock', $item)) {
            return false;
        }

        if (!array_key_exists('product_cost', $item)) {
            return false;
        }

        if (!array_key_exists('product_discontinued', $item)) {
            return false;
        }

        return true;
    }
}
