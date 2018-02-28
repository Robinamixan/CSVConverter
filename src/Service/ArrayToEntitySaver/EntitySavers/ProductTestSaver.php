<?php

namespace App\Service\ArrayToEntitySaver\EntitySavers;

use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityConverter\IArrayToEntityConverter;
use App\Service\EntityConverter\IEntityToArrayConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductTestSaver extends ProductSaver
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        IEntityToArrayConverter $productToArrayConverter,
        IArrayToEntityConverter $arrayToProductConverter
    ) {
        parent::__construct($entityManager, $entityConverter, $productToArrayConverter, $arrayToProductConverter);
    }

    public function saveItemsArrayIntoEntity(array $items): void
    {
        $this->validRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountSuccessfulInserts = 0;
        $this->checkValidRecordsFromItems($items);
        $this->removeRepeatedRecordsByCode();
        $this->checkIsValidRecordsInBD();
    }

    protected function checkIsValidRecordsInBD(): void
    {
        foreach ($this->validRecords as $validRecord) {
            if ($this->isInBD($validRecord)) {
                $this->addFailedRecord($validRecord);
                $this->amountSuccessfulInserts--;
            }
        }
    }
}
