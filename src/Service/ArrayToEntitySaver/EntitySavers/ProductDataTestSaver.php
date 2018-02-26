<?php

namespace App\Service\ArrayToEntitySaver\EntitySavers;

use App\Entity\ProductData;
use App\Service\EntityConverter\ArrayToEntityConverters\ArrayToProductDataConverter;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityConverter\EntityToArrayConverters\ProductDataToArrayConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDataTestSaver extends ProductDataSaver
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        ValidatorInterface $validator
    ) {
        parent::__construct($entityManager, $entityConverter, $validator);
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


        foreach ($this->validRecords as $validRecord) {
            if ($this->isInBD($validRecord)) {
                $this->failedRecords[] = $this->entityConverter->convertEntityToArray(
                    $validRecord,
                    new ProductDataToArrayConverter()
                );
                $this->amountSuccessfulRecords--;
                $this->amountFailedInserts++;
            }
        }
    }
}
