<?php

namespace App\Service\ArrayToEntitySaver\EntitySavers;

use App\Entity\Product;
use App\Service\EntityConverter\ArrayToEntityConverters\ArrayToProductConverter;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityConverter\EntityToArrayConverters\ProductToArrayConverter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductTestSaver extends ProductSaver
{
    public function __construct(
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        ValidatorInterface $validator
    ) {
        parent::__construct($entityManager, $entityConverter, $validator);
    }

    public function saveArrayIntoEntity(array $items): void
    {
        $this->validRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountSuccessfulInserts = 0;
        $this->checkValidRecordsFromItems($items);
        $this->checkIsValidRecordsInBD();
    }

    protected function checkIsValidRecordsInBD()
    {
        foreach ($this->validRecords as $validRecord) {
            if ($this->isInBD($validRecord)) {
                $this->failedRecords[] = $this->entityConverter->convertEntityToArray(
                    $validRecord,
                    new ProductToArrayConverter()
                );
                $this->amountSuccessfulInserts--;
                $this->amountFailedInserts++;
            }
        }
    }
}
