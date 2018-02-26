<?php

namespace App\Service\ArrayToEntitySaver\EntitySavers;

use App\Entity\Product;
use App\Service\EntityConverter\ArrayToEntityConverters\ArrayToProductConverter;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityConverter\EntityToArrayConverters\ProductToArrayConverter;
use App\Service\ArrayToEntitySaver\IEntitySaver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductSaver implements IEntitySaver
{
    protected $entityManager;
    protected $entityRepository;
    protected $entityConverter;
    protected $validator;
    protected $failedRecords;
    protected $validRecords;
    protected $amountSuccessfulInserts;
    protected $amountFailedInserts;

    public function __construct(
        EntityManagerInterface $entityManager,
        EntityConverter $entityConverter,
        ValidatorInterface $validator
    ) {
        $this->validRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountSuccessfulInserts = 0;
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(Product::class);
        $this->entityConverter = $entityConverter;
        $this->validator = $validator;
    }

    public function saveArrayIntoEntity(array $items): void
    {
        $this->validRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountSuccessfulInserts = 0;
        $this->checkValidRecordsFromItems($items);
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

    public function getAmountSuccessfulInserts(): int
    {
        return $this->amountSuccessfulInserts;
    }

    protected function checkValidRecordsFromItems(array $items): void
    {
        foreach ($items as $item) {
            $record = $this->entityConverter->convertArrayToEntity(
                $item,
                new ArrayToProductConverter()
            );
            if ($this->isValidRecord($record)) {
                $this->amountSuccessfulInserts++;
                $this->validRecords[] = $record;
            } else {
                $this->amountFailedInserts++;
                $this->failedRecords[] = $item;
            }
        }
    }

    protected function insertIntoBD(): void
    {

        for ($recordNumber = 0; $recordNumber < count($this->validRecords); $recordNumber++) {
            $this->entityManager->persist($this->validRecords[$recordNumber]);
        }

        try {
            $this->entityManager->flush();
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->reOpenEntityManager();

            for ($recordNumber = 0; $recordNumber < count($this->validRecords); $recordNumber++) {

                if ($this->isInBD($this->validRecords[$recordNumber])) {
                    //$this->entityManager->detach($this->validRecords[$recordNumber]);

                    $this->failedRecords[] = $this->entityConverter->convertEntityToArray(
                        $this->validRecords[$recordNumber],
                        new ProductToArrayConverter()
                    );

                    $this->amountSuccessfulInserts--;
                    $this->amountFailedInserts++;
                } else {
                    $this->entityManager->persist($this->validRecords[$recordNumber]);
                }
            }
            $this->entityManager->flush();
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

    protected function isValidRecord(Product $record): bool
    {
        $errors = $this->validator->validate($record);

        return empty(count($errors));
    }

    protected function isInBD(Product $item): bool
    {
        return $this->entityRepository->productCodeExists($item->getProductCode());
    }
}
