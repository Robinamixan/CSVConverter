<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 13.2.18
 * Time: 18.32
 */

namespace App\Service\ArrayToEntitySaver;

class ArrayToEntitySaver
{
    private $entitySaver;

    public function __construct()
    {
        $this->entitySaver = null;
    }

    public function saveArrayIntoEntity(array $items, IEntitySaver $entitySaver): void
    {
        $this->entitySaver = $entitySaver;
        $this->entitySaver->saveArrayIntoEntity($items);
    }

    public function getFailedRecords(): array
    {
        return $this->entitySaver->getFailedRecords();
    }

    public function getAmountFailedInserts(): int
    {
        return $this->entitySaver->getAmountFailedInserts();
    }

    public function getAmountSuccessfulInserts(): int
    {
        return $this->entitySaver->getAmountSuccessfulInserts();
    }
}
