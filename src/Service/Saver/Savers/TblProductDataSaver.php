<?php

namespace App\Service\Saver\Savers;


use App\Entity\TblProductData;
use App\Service\Saver\iSaver;
use Doctrine\ORM\EntityManagerInterface;

class TblProductDataSaver implements iSaver
{
    private $entityManager;
    private $entityRepository;
    private $failedRecords;
    private $rightRecords;
    private $amountProcessedRecords;
    private $amountSuccessfulRecords;
    private $amountFailedInserts;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->rightRecords = [];
        $this->failedRecords = [];
        $this->amountFailedInserts = 0;
        $this->amountProcessedRecords = 0;
        $this->amountSuccessfulRecords = 0;
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository(TblProductData::class);
    }

    public function saveArrayIntoEntity(array $contain): void
    {
        $contain = $this->convertContainToAssociativeArray($contain);
        $this->checkContain($contain);
        $this->insertIntoBD();
    }

    public function getFailedRecords():array
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

    private function convertContainToAssociativeArray(array $contain):array
    {
        $titles = $contain[0];
        $titles[] = "end of string";
        $tempArray = [];
        for ($i = 1; $i < count($contain); $i++) {
            for ($j = 0; $j < count($titles)-1; $j++) {
                if (key_exists($j, $contain[$i])) {
                    if ($contain[$i][$j] != '') {
                        $tempArray[$i][$titles[$j]] = $contain[$i][$j];
                    } else {
                        $tempArray[$i][$titles[$j]] = null;
                    }
                } else {
                    $tempArray[$i][$titles[$j]] = null;
                }
            }
        }
        return $tempArray;
    }

    private function checkContain(array $contain)
    {
        foreach ($contain as $item) {
            $this->amountProcessedRecords++;
            if ($this->isValidArray($item)) {
                $this->amountSuccessfulRecords++;

                $this->rightRecords[] = $this->entityRepository->createEntityFromArray($item);
            } else {
                $this->amountFailedInserts++;
                $this->failedRecords[] = $item;
            }
        }
    }

    private function insertIntoBD()
    {
        $step = 5;
        $lastStep = -1;
        for ($i = 0; $i < count($this->rightRecords); $i++) {
            $this->entityManager->persist($this->rightRecords[$i]);
            if ((($i % $step == 0) and ($i != 0)) or ($i == count($this->rightRecords)-1)) {

                try {
                    $this->entityManager->flush();
                } catch(\Doctrine\DBAL\DBALException $e) {

                    $this->reOpenEntityManager();

                    for ($j = $i; $j > $lastStep; $j--) {
                        if($this->isInBD($this->rightRecords[$j])) {
                            $this->entityManager->detach($this->rightRecords[$j]);
                            $this->failedRecords[] = $this->entityRepository->createArrayFromEntity($this->rightRecords[$j]);
                            $this->amountSuccessfulRecords--;
                            $this->amountFailedInserts++;
                        } else {
                            $this->entityManager->persist($this->rightRecords[$j]);
                        }
                    }

                    $this->entityManager->flush();
                }
                $lastStep = $i;
            }
        }
    }

    private function reOpenEntityManager()
    {
        if (!$this->entityManager->isOpen()) {

            $this->entityManager = $this->entityManager->create(
                $this->entityManager->getConnection(),
                $this->entityManager->getConfiguration()
            );
        }
    }

    private function isValidArray(array $item): bool
    {
        if(!$this->hasNeededField($item)) {
            return false;
        }

        if(!$this->hasNotEmptyFields($item)) {
            return false;
        }

        if(!$this->hasSpecialCondition($item)) {
            return false;
        }

        return true;
    }

    private function hasNotEmptyFields(array $item)
    {
        if ((empty($item['Product Name'])) || (empty($item['Product Code'])) || (empty($item['Product Description']))) {
            return false;
        }

        if ((intval($item['Stock']) == 0) || (is_null($item['Stock']))) {
            return false;
        }

        if ((floatval($item['Cost in GBP']) == 0) || (is_null($item['Cost in GBP']))) {
            return false;
        }
        return true;
    }

    private function hasSpecialCondition(array $item)
    {
        if ($item['Stock'] < 10) {
            return false;
        }

        if (($item['Cost in GBP'] < 5) || ($item['Cost in GBP'] > 1000)) {
            return false;
        }

        if (($item['Discontinued'] != 'yes') && (!is_null($item['Discontinued']))) {
            if (is_null($item['Discontinued'])) {
                return false;
            }
        }

        return true;
    }

    private function isInBD(TblProductData $item): bool
    {
        $record = $this->entityRepository->findByStrProductCode($item->getStrProductCode());

        if (!empty($record)) {
            return true;
        }
        return false;
    }

    private function hasNeededField(array $item): bool
    {
        if(!array_key_exists('Product Name', $item)) {
            return false;
        }
        if(!array_key_exists('Product Code', $item)) {
            return false;
        }
        if(!array_key_exists('Product Description', $item)) {
            return false;
        }
        if(!array_key_exists('Stock', $item)) {
            return false;
        }
        if(!array_key_exists('Cost in GBP', $item)) {
            return false;
        }
        return true;
    }
}